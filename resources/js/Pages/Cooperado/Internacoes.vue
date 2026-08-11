<script setup>
import CooperadoLayout from '@/Layouts/CooperadoLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref } from 'vue';

const props = defineProps({
    internacoes: Array,
});

const showForm = ref(false);

const statusMap = {
    analise:  { label: 'Em Análise',  cls: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10' },
    aprovada: { label: 'Aprovada',    cls: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10' },
    rejeitada:{ label: 'Rejeitada',   cls: 'bg-rose-50 text-rose-600 dark:bg-rose-500/10' },
};

const form = useForm({
    hospital_clinica: '',
    procedimento_principal: '',
    data_prevista: '',
    crm_medico: '',
    nome_medico: '',
    justificativa_clinica: '',
    anexo_guia: null,
});

const submitForm = () => {
    form.post(route('cooperado.internacoes.store'), {
        forceFormData: true,
        onSuccess: () => { form.reset(); showForm.value = false; },
    });
};
</script>

<template>
    <Head title="Internações Eletivas" />
    <CooperadoLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <Breadcrumbs :items="[{ label: 'Internações Eletivas' }]" />
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Internações Eletivas</h2>
                    <p class="text-sm text-[var(--text-muted)] mt-0.5">Solicite autorização para procedimentos cirúrgicos e internações.</p>
                </div>
                <button @click="showForm = !showForm" class="btn-primary flex items-center gap-2">
                    <span v-if="showForm">✕ Fechar</span>
                    <span v-else>＋ Nova Solicitação</span>
                </button>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Form Card -->
            <Transition name="slide-down">
                <div v-if="showForm" class="zynix-card">
                    <h3 class="text-sm font-black text-[var(--text-heading)] uppercase tracking-widest mb-6">📋 Nova Solicitação de Internação</h3>
                    <form @submit.prevent="submitForm" class="grid grid-cols-1 md:grid-cols-2 gap-5" enctype="multipart/form-data">
                        <div>
                            <InputLabel value="Hospital / Clínica" class="!font-black tracking-widest uppercase text-[10px]" />
                            <TextInput type="text" class="block w-full" v-model="form.hospital_clinica" required placeholder="Ex: Hospital Santa Casa" />
                            <InputError :message="form.errors.hospital_clinica" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Procedimento Principal" class="!font-black tracking-widest uppercase text-[10px]" />
                            <TextInput type="text" class="block w-full" v-model="form.procedimento_principal" required placeholder="Ex: Colecistectomia laparoscópica" />
                            <InputError :message="form.errors.procedimento_principal" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Data Prevista" class="!font-black tracking-widest uppercase text-[10px]" />
                            <TextInput type="date" class="block w-full" v-model="form.data_prevista" required />
                            <InputError :message="form.errors.data_prevista" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="CRM do Médico Solicitante" class="!font-black tracking-widest uppercase text-[10px]" />
                            <TextInput type="text" class="block w-full" v-model="form.crm_medico" required placeholder="CRM/AM 12345" />
                            <InputError :message="form.errors.crm_medico" class="mt-1" />
                        </div>
                        <div class="md:col-span-2">
                            <InputLabel value="Nome do Médico" class="!font-black tracking-widest uppercase text-[10px]" />
                            <TextInput type="text" class="block w-full" v-model="form.nome_medico" required placeholder="Dr. Nome Completo" />
                            <InputError :message="form.errors.nome_medico" class="mt-1" />
                        </div>
                        <div class="md:col-span-2">
                            <InputLabel value="Justificativa Clínica" class="!font-black tracking-widest uppercase text-[10px]" />
                            <textarea v-model="form.justificativa_clinica" rows="3" class="block w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-xs font-medium text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic transition-all p-3 resize-none" placeholder="Descreva brevemente a necessidade da internação..."></textarea>
                            <InputError :message="form.errors.justificativa_clinica" class="mt-1" />
                        </div>
                        <div class="md:col-span-2">
                            <InputLabel value="Guia Médica / Laudo (PDF, JPG — máx. 5MB)" class="!font-black tracking-widest uppercase text-[10px]" />
                            <input type="file" @input="form.anexo_guia = $event.target.files[0]" accept=".pdf,.jpg,.jpeg,.png"
                                class="block w-full text-xs font-medium text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-primary-light file:text-primary-dynamic hover:file:bg-primary-dynamic hover:file:text-white transition-all" />
                            <InputError :message="form.errors.anexo_guia" class="mt-1" />
                        </div>
                        <div class="md:col-span-2 flex justify-end gap-3 pt-4 border-t border-[var(--border-color)]">
                            <SecondaryButton type="button" @click="showForm = false">Cancelar</SecondaryButton>
                            <PrimaryButton :disabled="form.processing">Enviar Solicitação</PrimaryButton>
                        </div>
                    </form>
                </div>
            </Transition>

            <!-- Listagem -->
            <div class="zynix-card !p-0 overflow-hidden">
                <div class="p-5 border-b border-[var(--border-color)]">
                    <h3 class="text-sm font-black text-[var(--text-heading)] uppercase tracking-widest">Minhas Solicitações</h3>
                </div>
                <div v-if="internacoes.length === 0" class="p-12 text-center text-slate-400">
                    <p class="text-xs font-bold uppercase tracking-widest">Nenhuma solicitação realizada.</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="zynix-table w-full">
                        <thead>
                            <tr>
                                <th>Procedimento</th>
                                <th>Hospital</th>
                                <th>Médico</th>
                                <th>Data Prevista</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="i in internacoes" :key="i.id">
                                <td class="font-bold text-sm">{{ i.procedimento_principal }}</td>
                                <td class="text-xs text-slate-500">{{ i.hospital_clinica }}</td>
                                <td class="text-xs text-slate-500">{{ i.nome_medico }}</td>
                                <td class="text-xs text-slate-500">{{ new Date(i.data_prevista).toLocaleDateString('pt-BR') }}</td>
                                <td>
                                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest" :class="statusMap[i.status]?.cls">
                                        {{ statusMap[i.status]?.label || i.status }}
                                    </span>
                                    <p v-if="i.status === 'rejeitada' && i.motivo_rejeicao" class="text-[9px] text-rose-500 mt-1">{{ i.motivo_rejeicao }}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </CooperadoLayout>
</template>

<style scoped>
.slide-down-enter-active, .slide-down-leave-active { transition: all 0.3s ease; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-12px); }
</style>
