<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { 
    PlusIcon, 
    ReceiptRefundIcon,
    UserIcon,
    CheckBadgeIcon,
    ClockIcon,
    XCircleIcon,
    TrashIcon,
    XMarkIcon,
    ChevronDownIcon,
    CheckIcon,
    PaperAirplaneIcon
} from '@heroicons/vue/24/outline';
import { ref } from 'vue';

const props = defineProps({
    reembolsos: Object,
    associados: Array,
});

const showModal = ref(false);
const form = useForm({
    associado_id: '',
    valor_solicitado: 0,
    status: 'pendente',
    comprovantes: [],
});

const submit = () => {
    form.post(route('saude.reembolsos.store'), {
        onSuccess: () => { showModal.value = false; form.reset(); }
    });
};

const updateStatus = (reembolso, status) => {
    useForm({ status }).put(route('saude.reembolsos.update', reembolso.id));
};

const aprovarReembolso = (reembolsoId) => {
    useForm({}).post(route('saude.reembolsos.aprovar', reembolsoId));
};

const isRejectModalOpen = ref(false);
const rejectingReembolsoId = ref(null);
const rejectForm = useForm({
    motivo_recusa: ''
});

const confirmarRejeicao = (id) => {
    rejectingReembolsoId.value = id;
    rejectForm.reset();
    isRejectModalOpen.value = true;
};

const submitRejeicao = () => {
    rejectForm.post(route('saude.reembolsos.rejeitar', rejectingReembolsoId.value), {
        onSuccess: () => { isRejectModalOpen.value = false; }
    });
};

const deleteReembolso = (id) => {
    if (confirm('Excluir esta solicitação?')) {
        form.delete(route('saude.reembolsos.destroy', id));
    }
};
</script>

<template>
    <Head title="Reembolsos" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <Breadcrumbs :items="[{ label: 'Saúde' }, { label: 'Reembolsos' }]" />
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Solicitações de Reembolso</h2>
                    <p class="text-sm text-[var(--text-muted)] mt-0.5">Analise e gerencie pedidos de reembolso médico.</p>
                </div>
                <div>
                    <PrimaryButton @click="showModal = true" class="shadow-xl">
                        <PlusIcon class="w-4 h-4 mr-2" />
                        Nova Solicitação
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12">

                <!-- Reimbursements List -->
                <div class="zynix-card overflow-hidden !p-0">
                    <div class="overflow-x-auto">
                        <table class="zynix-table w-full">
                            <thead>
                                <tr>
                                    <th class="pl-8">Associado</th>
                                    <th class="text-center">Data</th>
                                    <th class="text-right">Valor</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-right pr-8">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in reembolsos.data" :key="item.id">
                                    <td class="pl-8">
                                        <div class="flex items-center">
                                            <div class="w-9 h-9 rounded-xl bg-primary-dynamic/10 flex items-center justify-center text-primary-dynamic font-black text-xs shadow-inner">
                                                {{ item.associado.nome.substring(0, 2).toUpperCase() }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="font-black text-[var(--text-heading)] tracking-tight">{{ item.associado.nome }}</div>
                                                <div v-if="item.descricao" class="text-[10px] text-slate-500 max-w-xs mt-1">Desc: {{ item.descricao }}</div>
                                                <div v-if="item.motivo_recusa" class="text-[9px] text-rose-500 font-bold max-w-xs mt-1 bg-rose-50 p-1 rounded">Motivo Recusa: {{ item.motivo_recusa }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                            {{ new Date(item.created_at).toLocaleDateString('pt-BR') }}
                                        </div>
                                    </td>
                                    <td class="text-right font-black text-[var(--text-heading)] tabular-nums">
                                        <span class="text-[10px] text-slate-400 font-bold mr-1 italic">R$</span>
                                        {{ parseFloat(item.valor_solicitado).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                                    </td>
                                    <td class="text-center">
                                        <span 
                                            :class="{
                                                'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10 border-emerald-100 dark:border-emerald-500/20': item.status === 'aprovado' || item.status === 'pago',
                                                'text-amber-600 bg-amber-50 dark:bg-amber-500/10 border-amber-100 dark:border-amber-500/20': item.status === 'pendente',
                                                'text-rose-600 bg-rose-50 dark:bg-rose-500/10 border-rose-100 dark:border-rose-500/20': item.status === 'rejeitado'
                                            }"
                                            class="px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border inline-flex items-center gap-1.5"
                                        >
                                            <span 
                                                class="w-1.5 h-1.5 rounded-full shadow-sm"
                                                :class="{
                                                    'bg-emerald-500': item.status === 'aprovado' || item.status === 'pago',
                                                    'bg-amber-500': item.status === 'pendente',
                                                    'bg-rose-500': item.status === 'rejeitado'
                                                }"
                                            ></span>
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="text-right pr-8">
                                        <div class="flex justify-end items-center space-x-1">
                                            <a v-if="Array.isArray(item.comprovantes) && item.comprovantes.length > 0" :href="route('cooperado.reembolsos.download', item.id)" target="_blank" class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-full transition-all" title="Ver Comprovante/NFe">
                                                <DocumentCheckIcon class="w-4 h-4" />
                                            </a>
                                            <!-- Auditoria Actions -->
                                            <button v-if="item.status === 'pendente'" @click="aprovarReembolso(item.id)" class="p-2 text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-full transition-all" title="Aprovar Valor">
                                                <CheckBadgeIcon class="w-4 h-4" />
                                            </button>
                                            <button v-if="item.status === 'pendente'" @click="confirmarRejeicao(item.id)" class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-full transition-all" title="Rejeitar NFe">
                                                <XMarkIcon class="w-4 h-4" />
                                            </button>
                                            <button @click="deleteReembolso(item.id)" class="p-2 text-rose-600 hover:bg-rose-100 dark:hover:bg-rose-600/10 rounded-full transition-all" title="Excluir do Sistema">
                                                <TrashIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="reembolsos.data.length === 0">
                                    <td colspan="5" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="p-4 rounded-2xl bg-primary-dynamic/10 text-primary-dynamic mb-4">
                                                <ReceiptRefundIcon class="w-8 h-8" />
                                            </div>
                                            <p class="font-black text-slate-400 uppercase tracking-widest text-xs">Nenhuma solicitação registrada</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
        </div>

        <!-- Add Modal -->
        <Modal :show="showModal" @close="showModal = false" max-width="md">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <p class="text-[10px] font-black text-primary-dynamic uppercase tracking-[0.2em] mb-1">Auditoria de Saúde</p>
                        <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tighter">
                            Nova Solicitação
                        </h2>
                    </div>
                    <button @click="showModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>
                
                <form @submit.prevent="submit" class="space-y-8">
                    <div class="space-y-6">
                        <div>
                            <InputLabel value="Associado Solicitante *" />
                            <div class="relative">
                                <select v-model="form.associado_id" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic py-3 text-sm font-medium pr-10 appearance-none" required>
                                    <option value="" disabled>Selecione o cooperado</option>
                                    <option v-for="a in associados" :key="a.id" :value="a.id">{{ a.nome }}</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                    <ChevronDownIcon class="w-4 h-4" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <InputLabel value="Valor da Despesa (R$) *" />
                            <TextInput v-model="form.valor_solicitado" type="number" step="0.01" class="block w-full font-black text-lg bg-[var(--bg-main)]" placeholder="0,00" required />
                        </div>

                        <div class="p-6 bg-amber-50 dark:bg-amber-500/5 rounded-2xl border border-amber-100 dark:border-amber-500/10">
                            <p class="text-[10px] font-black text-amber-700 dark:text-amber-400 mb-2 flex items-center uppercase tracking-[0.2em]">
                                <ClockIcon class="w-4 h-4 mr-2" /> status inicial
                            </p>
                            <p class="text-xs text-amber-600 dark:text-amber-500/80 font-medium italic leading-relaxed">Sua solicitação entrará em fila de auditoria para conferência de documentos e coberturas do plano.</p>
                        </div>
                    </div>

                    <div class="pt-6 flex items-center justify-end space-x-3 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="showModal = false" type="button" class="!rounded-full !py-4 !px-8">
                            <XMarkIcon class="w-4 h-4 mr-2" /> Cancelar
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing" class="!rounded-full !py-4 !px-8 shadow-xl shadow-primary-dynamic/20">
                            <CheckIcon class="w-4 h-4 mr-2" /> Enviar Solicitação
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Rejeição Modal -->
        <Modal :show="isRejectModalOpen" @close="isRejectModalOpen = false">
            <div class="p-8">
                <h2 class="text-xl font-black text-rose-500 mb-8 flex items-center">
                    Auditoria Negada
                </h2>
                <p class="text-xs text-slate-500 mb-6 font-medium">Informe ao cooperado o motivo da recusa (Ex: NFe ilegível, CPF do dependente não cadastrado, etc). Ele receberá um e-mail com as instruções para reemissão do chamado.</p>
                
                <form @submit.prevent="submitRejeicao" class="space-y-4">
                    <div>
                        <InputLabel value="Motivo da Restrição:" class="!text-rose-500 uppercase tracking-widest !font-black !text-[10px]" />
                        <textarea v-model="rejectForm.motivo_recusa" rows="3" class="mt-2 block w-full text-sm rounded-xl border-slate-200 focus:border-rose-500 focus:ring focus:ring-rose-200 focus:ring-opacity-50" placeholder="Motivo que será enviado para o cooperado..." required></textarea>
                        <InputError :message="rejectForm.errors.motivo_recusa" />
                    </div>
                    <div class="flex justify-end pt-6 gap-3 border-t border-[var(--border-color)] mt-4">
                        <SecondaryButton @click="isRejectModalOpen = false" type="button">
                            <XMarkIcon class="w-4 h-4 mr-2" /> Cancelar
                        </SecondaryButton>
                        <PrimaryButton type="submit" class="!bg-rose-600 hover:!bg-rose-700" :disabled="rejectForm.processing">
                            <XCircleIcon class="w-4 h-4 mr-2" /> Confirmar Rejeição
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
