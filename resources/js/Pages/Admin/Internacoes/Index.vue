<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    internacoes: Object, // paginated
});

const statusMap = {
    analise:  { label: 'Em Análise',  cls: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10' },
    aprovada: { label: 'Aprovada',    cls: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10' },
    rejeitada:{ label: 'Rejeitada',   cls: 'bg-rose-50 text-rose-600 dark:bg-rose-500/10' },
};

const rejectForm = useForm({ motivo_rejeicao: '' });
const rejectingId = ref(null);

const aprovar = (id) => router.post(route('saude.internacoes.aprovar', id), {}, { preserveScroll: true });
const openReject = (id) => { rejectingId.value = id; rejectForm.motivo_rejeicao = ''; };
const submitRejeitar = () => {
    rejectForm.post(route('saude.internacoes.rejeitar', rejectingId.value), {
        preserveScroll: true,
        onSuccess: () => { rejectingId.value = null; },
    });
};
</script>

<template>
    <Head title="Gestão de Internações" />
    <AuthenticatedLayout>
        <template #header>
            <Breadcrumbs :items="[{ label: 'Clube de Saúde' }, { label: 'Internações' }]" />
            <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Internações Eletivas</h2>
            <p class="text-sm text-[var(--text-muted)] mt-0.5">Gerencie as solicitações de internação dos clientes.</p>
        </template>

        <div class="pb-12">
            <!-- Rejeitar Modal -->
            <div v-if="rejectingId" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="rejectingId = null">
                <div class="bg-[var(--bg-card)] rounded-2xl shadow-2xl p-8 w-full max-w-md">
                    <h3 class="text-base font-black text-[var(--text-heading)] mb-4">Rejeitar Internação</h3>
                    <div class="mb-4">
                        <InputLabel value="Motivo da Rejeição" class="!font-black tracking-widest uppercase text-[10px]" />
                        <textarea v-model="rejectForm.motivo_rejeicao" rows="4" required
                            class="block w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-xs font-medium text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic p-3 resize-none"
                            placeholder="Descreva o motivo da rejeição..."></textarea>
                        <InputError :message="rejectForm.errors.motivo_rejeicao" class="mt-1" />
                    </div>
                    <div class="flex justify-end gap-3">
                        <SecondaryButton @click="rejectingId = null">Cancelar</SecondaryButton>
                        <PrimaryButton class="!bg-rose-600 hover:!bg-rose-700" @click="submitRejeitar" :disabled="rejectForm.processing">Confirmar Rejeição</PrimaryButton>
                    </div>
                </div>
            </div>

            <div class="zynix-card !p-0 overflow-hidden">
                <div class="p-5 border-b border-[var(--border-color)]">
                    <h3 class="text-sm font-black text-[var(--text-heading)] uppercase tracking-widest">Solicitações de Internação</h3>
                </div>

                <div v-if="internacoes.data.length === 0" class="p-12 text-center">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Nenhuma solicitação recebida.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="zynix-table w-full">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Procedimento</th>
                                <th>Hospital</th>
                                <th>Médico</th>
                                <th>Data Prevista</th>
                                <th>Status</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="int in internacoes.data" :key="int.id">
                                <td class="font-bold text-sm">{{ int.user?.name }}</td>
                                <td class="text-xs font-bold">{{ int.procedimento_principal }}</td>
                                <td class="text-xs text-slate-500">{{ int.hospital_clinica }}</td>
                                <td class="text-xs text-slate-500">{{ int.nome_medico }} <span class="text-slate-400">({{ int.crm_medico }})</span></td>
                                <td class="text-xs text-slate-500">{{ new Date(int.data_prevista).toLocaleDateString('pt-BR') }}</td>
                                <td>
                                    <div>
                                        <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest" :class="statusMap[int.status]?.cls">
                                            {{ statusMap[int.status]?.label || int.status }}
                                        </span>
                                        <p v-if="int.status === 'rejeitada' && int.motivo_rejeicao" class="text-[9px] text-rose-400 mt-1 max-w-[140px] truncate" :title="int.motivo_rejeicao">{{ int.motivo_rejeicao }}</p>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <div v-if="int.status === 'analise'" class="flex justify-end gap-2">
                                        <button @click="aprovar(int.id)"
                                            class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 text-[10px] font-black hover:bg-emerald-100 dark:bg-emerald-500/10 transition-all">
                                            Aprovar
                                        </button>
                                        <button @click="openReject(int.id)"
                                            class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-600 text-[10px] font-black hover:bg-rose-100 dark:bg-rose-500/10 transition-all">
                                            Rejeitar
                                        </button>
                                    </div>
                                    <span v-else class="text-[9px] text-slate-400 uppercase">—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
