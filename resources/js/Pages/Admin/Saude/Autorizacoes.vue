<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    DocumentMagnifyingGlassIcon,
    UserIcon,
    BuildingStorefrontIcon
} from '@heroicons/vue/24/outline';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    solicitacoes: Array
});

const formAprovacao = useForm({});
const formRecusa = useForm({
    motivo: ''
});

const isRecusaModalOpen = ref(false);
const solicitacaoSelecionada = ref(null);

const aprovar = (id) => {
    formAprovacao.post(route('saude.autorizacoes.aprovar', id), {
        preserveScroll: true
    });
};

const abrirModalRecusa = (solicitacao) => {
    solicitacaoSelecionada.value = solicitacao;
    formRecusa.motivo = '';
    isRecusaModalOpen.value = true;
};

const confirmarRecusa = () => {
    formRecusa.post(route('saude.autorizacoes.rejeitar', solicitacaoSelecionada.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isRecusaModalOpen.value = false;
        }
    });
};

const formatCurrency = (value) => {
    if (!value) return '-';
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};
</script>

<template>
    <Head title="Autorizações de Saúde" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">Autorizações Pendentes</h2>
                    <p class="text-sm text-[var(--text-muted)] mt-1 font-medium">Gestão de aprovações da rede credenciada (Fluxo Invertido).</p>
                </div>
                <div class="p-3 bg-[var(--bg-card)] rounded-xl border border-[var(--border-color)]">
                    <DocumentMagnifyingGlassIcon class="w-6 h-6 text-primary-dynamic" />
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div v-if="$page.props.flash?.success" class="bg-emerald-50 text-emerald-700 p-4 rounded-xl border border-emerald-100 flex items-center">
                    <CheckCircleIcon class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span class="text-sm font-bold">{{ $page.props.flash.success }}</span>
                </div>

                <div class="bg-[var(--bg-card)] overflow-hidden shadow-sm rounded-2xl border border-[var(--border-color)]">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[var(--border-color)]">
                            <thead class="bg-slate-50/50 dark:bg-slate-800/50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Paciente (Cooperado)</th>
                                    <th scope="col" class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Prestador / Serviço</th>
                                    <th scope="col" class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Valor</th>
                                    <th scope="col" class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                                    <th scope="col" class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Ações (1-Click)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--border-color)] bg-transparent">
                                <tr v-for="sol in solicitacoes" :key="sol.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-primary-dynamic/10 rounded-full flex items-center justify-center">
                                                <UserIcon class="h-5 w-5 text-primary-dynamic" />
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-black text-[var(--text-heading)]">{{ sol.paciente }}</div>
                                                <div class="text-xs text-[var(--text-muted)] font-bold mt-0.5">Matrícula: {{ sol.matricula }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-black text-[var(--text-heading)] flex items-center">
                                            <BuildingStorefrontIcon class="w-4 h-4 mr-1.5 text-slate-400" />
                                            {{ sol.prestador }}
                                        </div>
                                        <div class="text-xs text-[var(--text-muted)] font-medium mt-1 max-w-xs truncate" :title="sol.observacoes">
                                            Serviço: {{ sol.servico }}
                                            <span v-if="sol.observacoes" class="block text-amber-600 dark:text-amber-400 font-bold mt-0.5">Obs: {{ sol.observacoes }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-[var(--text-heading)]">{{ formatCurrency(sol.valor) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-if="sol.status === 'pendente_autorizacao'" class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-amber-100 text-amber-800">
                                            <ClockIcon class="w-3 h-3 mr-1" /> Pendente
                                        </span>
                                        <span v-else-if="sol.status === 'aprovado_cooperativa'" class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-800">
                                            <CheckCircleIcon class="w-3 h-3 mr-1" /> Aprovado
                                        </span>
                                        <span v-else-if="sol.status === 'negado'" class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-rose-100 text-rose-800">
                                            <XCircleIcon class="w-3 h-3 mr-1" /> Negado
                                        </span>
                                        <span v-else class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-blue-100 text-blue-800">
                                            {{ sol.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div v-if="sol.status === 'pendente_autorizacao'" class="flex items-center justify-end gap-2">
                                            <button @click="abrirModalRecusa(sol)" class="text-rose-500 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg transition-colors text-xs font-black">
                                                Rejeitar
                                            </button>
                                            <button @click="aprovar(sol.id)" class="text-white bg-emerald-500 hover:bg-emerald-600 px-4 py-1.5 rounded-lg transition-colors text-xs font-black shadow-lg shadow-emerald-500/20" :disabled="formAprovacao.processing">
                                                Aprovar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="solicitacoes.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                        Nenhuma solicitação encontrada no momento.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Recusa -->
        <Modal :show="isRecusaModalOpen" @close="isRecusaModalOpen = false" maxWidth="md">
            <div class="p-6">
                <h2 class="text-xl font-black text-rose-600 mb-4 flex items-center">
                    <XCircleIcon class="w-6 h-6 mr-2" /> Rejeitar Autorização
                </h2>
                
                <p class="text-sm text-slate-600 mb-4">
                    Informe o motivo da recusa. Esta informação ficará visível para o cooperado.
                </p>

                <form @submit.prevent="confirmarRecusa">
                    <textarea 
                        v-model="formRecusa.motivo" 
                        class="w-full rounded-xl border-slate-200 text-sm focus:border-rose-500 focus:ring focus:ring-rose-200 resize-none mb-4"
                        rows="3"
                        placeholder="Ex: Não coberto pelo plano, Limite excedido..."
                        required
                    ></textarea>
                    
                    <div class="flex justify-end gap-3">
                        <SecondaryButton @click="isRecusaModalOpen = false">Cancelar</SecondaryButton>
                        <PrimaryButton type="submit" class="!bg-rose-600 hover:!bg-rose-700 focus:!ring-rose-500 shadow-rose-500/30" :disabled="formRecusa.processing">
                            Confirmar Recusa
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
