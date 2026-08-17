<script setup>
import CooperadoLayout from '@/Layouts/CooperadoLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { CalendarDaysIcon, ClockIcon, CheckCircleIcon, XCircleIcon, PlusIcon, QrCodeIcon, PaperClipIcon } from '@heroicons/vue/24/outline';
import { ref, computed } from 'vue';

const props = defineProps({
    agendamentos: Array,
    paymentConfig: Object,
});

const statusColor = (status) => ({
    pendente:   'bg-amber-50 text-amber-600 dark:bg-amber-500/10',
    confirmado: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10',
    realizado:  'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10',
    cancelado:  'bg-rose-50 text-rose-600 dark:bg-rose-500/10',
});

const tipoLabel = (tipo) => tipo === 'consulta' ? '🩺 Consulta' : '🔬 Exame';

const selectedAgendamento = ref(null);
const showPixModal = ref(false);
const copied = ref(false);

const proofForm = useForm({
    comprovante_pix: null,
});

function openPixModal(ag) {
    selectedAgendamento.value = ag;
    proofForm.reset();
    showPixModal.value = true;
}

function closePixModal() {
    showPixModal.value = false;
    selectedAgendamento.value = null;
}

function copyPixKey() {
    const key = props.paymentConfig?.pix_manual?.chave_pix || '';
    if (key) {
        navigator.clipboard.writeText(key);
        copied.value = true;
        setTimeout(() => copied.value = false, 2000);
    }
}

function submitComprovante() {
    if (!selectedAgendamento.value) return;
    proofForm.post(route('cooperado.agendamentos.comprovante', selectedAgendamento.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closePixModal();
        }
    });
}
</script>

<template>
    <Head title="Meus Agendamentos" />
    <CooperadoLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <Breadcrumbs :items="[{ label: 'Agendamentos' }]" />
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Meus Agendamentos</h2>
                    <p class="text-sm text-[var(--text-muted)] mt-0.5">Acompanhe consultas e exames solicitados.</p>
                </div>
                <Link :href="route('cooperado.agendamentos.create')" class="btn-primary flex items-center gap-2">
                    <PlusIcon class="w-4 h-4" />
                    Novo Agendamento
                </Link>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Empty State -->
            <div v-if="agendamentos.length === 0" class="zynix-card flex flex-col items-center justify-center py-16 text-center">
                <CalendarDaysIcon class="w-12 h-12 text-slate-300 mb-4" />
                <h3 class="text-sm font-black text-[var(--text-heading)] uppercase tracking-widest mb-2">Nenhum agendamento</h3>
                <p class="text-xs text-slate-400 font-medium mb-6">Você ainda não possui consultas ou exames agendados.</p>
                <Link :href="route('cooperado.agendamentos.create')" class="btn-primary flex items-center gap-2">
                    <PlusIcon class="w-4 h-4" />
                    Agendar Agora
                </Link>
            </div>

            <!-- Grid of cards -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                <div
                    v-for="ag in agendamentos"
                    :key="ag.id"
                    class="zynix-card relative overflow-hidden flex flex-col justify-between"
                >
                    <div>
                        <!-- Type Badge -->
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-black uppercase tracking-widest text-primary-dynamic">{{ tipoLabel(ag.tipo) }}</span>
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest" :class="statusColor(ag.status)">
                                {{ ag.status }}
                            </span>
                        </div>

                        <h3 class="text-base font-black text-[var(--text-heading)] leading-tight mb-1">
                            {{ ag.tabela_servico?.nome || ag.especialidade }}
                        </h3>
                        <p v-if="ag.tabela_servico" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Especialidade: {{ ag.especialidade }}</p>
                        <p v-if="ag.prestador" class="text-xs font-bold text-slate-400 mb-3">Prestador: {{ ag.prestador.nome }}</p>

                        <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mt-3">
                            <CalendarDaysIcon class="w-4 h-4" />
                            {{ new Date(ag.data_hora).toLocaleDateString('pt-BR') }}
                            <ClockIcon class="w-4 h-4 ml-2" />
                            {{ new Date(ag.data_hora).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) }}
                        </div>

                        <!-- Payment Details Box -->
                        <div class="mt-4 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-[var(--border-color)] space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Valor do Serviço</span>
                                <span class="text-sm font-black text-[var(--text-heading)] font-mono">
                                    {{ (ag.valor_cobrado || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-2 pt-2 border-t border-[var(--border-color)]">
                                <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-primary-dynamic/10 text-primary-dynamic uppercase tracking-wider">
                                    {{ ag.status_pagamento_label }}
                                </span>

                                <button 
                                    v-if="ag.forma_pagamento === 'pix_manual' || !ag.comprovante_pix_url"
                                    @click="openPixModal(ag)" 
                                    class="px-2.5 py-1 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-lg text-[10px] font-black uppercase tracking-wider flex items-center gap-1 transition-all"
                                >
                                    <QrCodeIcon class="w-3.5 h-3.5" />
                                    {{ ag.comprovante_pix_url ? 'Ver Chave PIX' : 'Pagar / Anexar' }}
                                </button>
                            </div>

                            <div v-if="ag.comprovante_pix_url" class="pt-1 flex items-center justify-between text-[10px]">
                                <span class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
                                    <CheckCircleIcon class="w-3.5 h-3.5" /> Comprovante Anexado
                                </span>
                                <a :href="ag.comprovante_pix_url" target="_blank" class="text-primary-dynamic hover:underline font-bold flex items-center gap-1">
                                    <PaperClipIcon class="w-3 h-3" /> Visualizar
                                </a>
                            </div>
                        </div>

                        <p v-if="ag.observacoes" class="mt-3 text-[10px] text-slate-400 italic leading-relaxed">
                            {{ ag.observacoes }}
                        </p>
                    </div>

                    <!-- Cancel action -->
                    <div v-if="ag.status === 'pendente'" class="mt-4 pt-3 border-t border-[var(--border-color)] flex justify-between items-center">
                        <Link
                            :href="route('cooperado.agendamentos.destroy', ag.id)"
                            method="delete"
                            as="button"
                            class="text-[10px] font-black uppercase tracking-widest text-rose-500 hover:text-rose-700 transition-colors"
                        >
                            Cancelar solicitação
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Pagamento PIX -->
        <Modal :show="showPixModal" @close="closePixModal" maxWidth="md">
            <div class="p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-[var(--border-color)] pb-3">
                    <h3 class="text-base font-black text-[var(--text-heading)]">Pagamento via PIX (Santa Casa)</h3>
                    <button @click="closePixModal" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <div v-if="selectedAgendamento" class="space-y-4">
                    <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase">Serviço</p>
                            <p class="text-xs font-bold text-[var(--text-heading)]">{{ selectedAgendamento.especialidade }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-black text-slate-400 uppercase">Valor</p>
                            <p class="text-sm font-black text-emerald-600 font-mono">
                                {{ (selectedAgendamento.valor_cobrado || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) }}
                            </p>
                        </div>
                    </div>

                    <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase text-emerald-700 dark:text-emerald-300">Chave PIX Santa Casa</span>
                            <span class="text-[10px] font-bold text-slate-500">Favorecido: {{ paymentConfig?.pix_manual?.favorecido || $page.props.tenant?.nome }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <input 
                                type="text" 
                                readonly 
                                :value="paymentConfig?.pix_manual?.chave_pix || 'Consulte na Recepção'" 
                                class="w-full bg-white dark:bg-slate-900 border border-[var(--border-color)] rounded-xl py-2 px-3 text-xs font-mono font-bold" 
                            />
                            <button type="button" @click="copyPixKey" class="px-3 py-2 bg-emerald-500 text-white rounded-xl text-xs font-bold shrink-0">
                                {{ copied ? 'Copiado!' : 'Copiar' }}
                            </button>
                        </div>
                    </div>

                    <form @submit.prevent="submitComprovante" class="space-y-3 pt-2 border-t border-[var(--border-color)]">
                        <InputLabel value="Enviar Comprovante de Pagamento PIX" class="!font-black text-[10px] uppercase" />
                        <input 
                            type="file" 
                            @change="e => proofForm.comprovante_pix = e.target.files[0]" 
                            accept="image/*,application/pdf"
                            required
                            class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-500 file:text-white hover:file:bg-emerald-600 cursor-pointer"
                        />
                        
                        <div class="flex justify-end gap-2 pt-3">
                            <SecondaryButton type="button" @click="closePixModal">Fechar</SecondaryButton>
                            <PrimaryButton :disabled="proofForm.processing" class="!bg-emerald-500 hover:!bg-emerald-600">
                                Confirmar Envio
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </Modal>
    </CooperadoLayout>
</template>
