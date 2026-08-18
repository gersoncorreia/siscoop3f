<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { 
    CheckCircleIcon, 
    XCircleIcon, 
    CalendarDaysIcon, 
    EyeIcon, 
    PaperClipIcon, 
    ClockIcon, 
    CurrencyDollarIcon,
    UserIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';
import { ref } from 'vue';

const props = defineProps({
    agendamentos: Object,
    stats: Object,
});

const statusColor = (status) => ({
    pendente:   'bg-amber-50 text-amber-600 dark:bg-amber-500/10 border-amber-200',
    confirmado: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 border-blue-200',
    realizado:  'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 border-emerald-200',
    cancelado:  'bg-rose-50 text-rose-600 dark:bg-rose-500/10 border-rose-200',
});

const selectedAgendamento = ref(null);
const showModal = ref(false);
const isRecusando = ref(false);

const approveForm = useForm({
    data_hora: '',
    observacoes: '',
});

const recusarForm = useForm({
    motivo: '',
});

function openModal(ag) {
    selectedAgendamento.value = ag;
    isRecusando.value = false;
    
    // Preencher formulário de aprovação com a data/hora atual
    const dt = ag.data_hora ? new Date(ag.data_hora) : new Date();
    dt.setMinutes(dt.getMinutes() - dt.getTimezoneOffset());
    approveForm.data_hora = dt.toISOString().slice(0, 16);
    approveForm.observacoes = ag.observacoes || '';
    recusarForm.reset();

    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    selectedAgendamento.value = null;
    isRecusando.value = false;
}

function submitAprovacao() {
    if (!selectedAgendamento.value) return;
    approveForm.post(route('saude.agendamentos.aprovar', selectedAgendamento.value.id), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
}

function submitRecusa() {
    if (!selectedAgendamento.value) return;
    recusarForm.post(route('saude.agendamentos.recusar', selectedAgendamento.value.id), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
}

const realizar = (id) => router.post(route('saude.agendamentos.realizar', id), {}, { preserveScroll: true });
</script>

<template>
    <Head title="Gestão de Agendamentos" />
    <AuthenticatedLayout>
        <template #header>
            <Breadcrumbs :items="[{ label: 'Clube de Saúde' }, { label: 'Agendamentos' }]" />
            <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Gestão de Agendamentos (Santa Casa)</h2>
            <p class="text-sm text-[var(--text-muted)] mt-0.5">Validação de consultas, exames, disponibilidades e pagamentos dos clientes.</p>
        </template>

        <div class="space-y-6 pb-12">
            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="zynix-card p-5 border-l-4 border-amber-500 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pendentes de Análise</p>
                        <p class="text-2xl font-black text-amber-600 mt-1">{{ stats?.totalPendentes || 0 }}</p>
                    </div>
                    <div class="p-3 bg-amber-50 dark:bg-amber-500/10 rounded-xl text-amber-500">
                        <ClockIcon class="w-6 h-6" />
                    </div>
                </div>

                <div class="zynix-card p-5 border-l-4 border-emerald-500 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Validação PIX Pendente</p>
                        <p class="text-2xl font-black text-emerald-600 mt-1">{{ stats?.totalPixPendente || 0 }}</p>
                    </div>
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-500/10 rounded-xl text-emerald-500">
                        <PaperClipIcon class="w-6 h-6" />
                    </div>
                </div>

                <div class="zynix-card p-5 border-l-4 border-blue-500 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Agendamentos Confirmados</p>
                        <p class="text-2xl font-black text-blue-600 mt-1">{{ stats?.totalConfirmados || 0 }}</p>
                    </div>
                    <div class="p-3 bg-blue-50 dark:bg-blue-500/10 rounded-xl text-blue-500">
                        <CheckCircleIcon class="w-6 h-6" />
                    </div>
                </div>

                <div class="zynix-card p-5 border-l-4 border-primary-dynamic flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Solicitado no Mês</p>
                        <p class="text-2xl font-black text-primary-dynamic mt-1">{{ stats?.totalMes || 0 }}</p>
                    </div>
                    <div class="p-3 bg-primary-dynamic/10 rounded-xl text-primary-dynamic">
                        <CalendarDaysIcon class="w-6 h-6" />
                    </div>
                </div>
            </div>

            <!-- Tabela Principal -->
            <div class="zynix-card !p-0 overflow-hidden">
                <div class="p-5 border-b border-[var(--border-color)] flex items-center justify-between">
                    <h3 class="text-sm font-black text-[var(--text-heading)] uppercase tracking-widest">Solicitações de Agendamento</h3>
                </div>

                <div v-if="agendamentos.data.length === 0" class="p-12 text-center">
                    <CalendarDaysIcon class="w-10 h-10 text-slate-300 mx-auto mb-3" />
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Nenhum agendamento registrado.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="zynix-table w-full">
                        <thead>
                            <tr>
                                <th>Cliente / Solicitante</th>
                                <th>Serviço / Especialidade</th>
                                <th>Prestador</th>
                                <th>Data / Hora Solicitada</th>
                                <th>Forma de Pagamento</th>
                                <th>Status</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="ag in agendamentos.data" :key="ag.id">
                                <td>
                                    <div class="flex flex-col">
                                        <span class="font-black text-xs text-[var(--text-heading)]">{{ ag.cliente?.name }}</span>
                                        <span class="text-[10px] text-slate-400 font-medium">CPF: {{ ag.cliente?.cpf || '—' }} | Matrícula #{{ ag.cliente?.matricula || 'N/I' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-xs">{{ ag.tabela_servico?.nome || ag.especialidade }}</span>
                                        <span class="text-[9px] text-slate-400 uppercase tracking-wider">Código: {{ ag.tabela_servico?.codigo || 'S/C' }} | {{ ag.tipo }}</span>
                                    </div>
                                </td>
                                <td class="text-xs text-slate-500 font-medium">{{ ag.prestador?.nome || 'Prestador Padrão' }}</td>
                                <td class="text-xs font-bold text-slate-600 dark:text-slate-300">
                                    {{ new Date(ag.data_hora).toLocaleDateString('pt-BR') }}
                                    <span class="text-slate-400 font-normal"> {{ new Date(ag.data_hora).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) }}</span>
                                </td>
                                <td>
                                    <div class="flex flex-col items-start gap-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                            {{ ag.forma_pagamento_label }}
                                        </span>
                                        <span v-if="ag.comprovante_pix_url" class="text-[9px] font-black text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                                            <PaperClipIcon class="w-3 h-3" /> Comprovante PIX Anexado
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border" :class="statusColor(ag.status)">
                                        {{ ag.status }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <button 
                                            @click="openModal(ag)"
                                            :class="[
                                                'px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 transition-all',
                                                ag.status === 'pendente' ? 'bg-primary-dynamic/10 hover:bg-primary-dynamic/20 text-primary-dynamic' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
                                            ]" 
                                            :title="ag.status === 'pendente' ? 'Analisar e Validar' : 'Ver Detalhes'"
                                        >
                                            <EyeIcon class="w-4 h-4" />
                                            {{ ag.status === 'pendente' ? 'Analisar / Validar' : (ag.status === 'confirmado' ? 'Detalhes / Concluir' : 'Ver Histórico') }}
                                        </button>
                                        <button v-if="ag.status === 'confirmado'" @click="realizar(ag.id)"
                                            class="px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-500/10 text-xs font-bold transition-all flex items-center gap-1" title="Marcar como Realizado">
                                            ✅ Concluir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal de Análise & Aprovação / Reagendamento / Leitura -->
        <Modal :show="showModal" @close="closeModal" maxWidth="2xl">
            <div v-if="selectedAgendamento" class="p-6 space-y-6">
                <!-- Cabeçalho do Modal -->
                <div class="flex items-center justify-between border-b border-[var(--border-color)] pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black uppercase text-primary-dynamic tracking-widest">
                                {{ ['realizado', 'cancelado'].includes(selectedAgendamento.status) ? 'Histórico do Agendamento' : 'Análise de Solicitação' }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest border" :class="statusColor(selectedAgendamento.status)">
                                {{ selectedAgendamento.status }}
                            </span>
                        </div>
                        <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight mt-1">
                            {{ selectedAgendamento.tabela_servico?.nome || selectedAgendamento.especialidade }}
                        </h3>
                    </div>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600 font-black text-lg">✕</button>
                </div>

                <!-- Banner Informativo para Agendamentos Concluídos ou Cancelados -->
                <div v-if="selectedAgendamento.status === 'realizado'" class="p-3.5 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl flex items-center gap-3 text-xs text-emerald-700 dark:text-emerald-300 font-bold">
                    <CheckCircleIcon class="w-5 h-5 text-emerald-600 shrink-0" />
                    <span>✅ Este atendimento já foi REALIZADO e finalizado. As informações estão salvas em modo de leitura para auditoria.</span>
                </div>

                <div v-if="selectedAgendamento.status === 'cancelado'" class="p-3.5 bg-rose-500/10 border border-rose-500/20 rounded-2xl flex items-center gap-3 text-xs text-rose-700 dark:text-rose-300 font-bold">
                    <XCircleIcon class="w-5 h-5 text-rose-600 shrink-0" />
                    <span>❌ Este agendamento foi CANCELADO. Exibindo histórico e justificativa em modo de leitura.</span>
                </div>

                <!-- Seção 1: Dados do Cliente & Contato -->
                <div class="p-4 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-[var(--border-color)] space-y-3">
                    <div class="flex items-center gap-2 text-xs font-black text-slate-400 uppercase tracking-widest border-b border-[var(--border-color)] pb-2">
                        <UserIcon class="w-4 h-4 text-primary-dynamic" /> Dados do Solicitante (Cliente Santa Casa)
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <div>
                            <span class="text-[10px] font-black text-slate-400 uppercase block">Nome</span>
                            <span class="font-bold text-[var(--text-heading)]">{{ selectedAgendamento.cliente?.name }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-black text-slate-400 uppercase block">CPF / Matrícula</span>
                            <span class="font-bold text-[var(--text-heading)]">{{ selectedAgendamento.cliente?.cpf || '—' }} (Mat. #{{ selectedAgendamento.cliente?.matricula || 'N/I' }})</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-black text-slate-400 uppercase block">Telefone de Contato</span>
                            <span class="font-bold text-[var(--text-heading)]">{{ selectedAgendamento.cliente?.telefone }}</span>
                        </div>
                    </div>
                </div>

                <!-- Seção 2: Validação Financeira & Comprovante PIX -->
                <div class="p-4 bg-emerald-500/5 border border-emerald-500/20 rounded-2xl space-y-3">
                    <div class="flex items-center justify-between border-b border-emerald-500/20 pb-2">
                        <span class="text-xs font-black uppercase tracking-widest text-emerald-700 dark:text-emerald-400 flex items-center gap-2">
                            <CurrencyDollarIcon class="w-4 h-4" /> Informações Financeiras & Pagamento
                        </span>
                        <span class="text-sm font-black text-emerald-600 font-mono">
                            {{ (selectedAgendamento.valor_cobrado || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-[10px] font-black text-slate-400 uppercase block">Forma Escolhida pelo Cliente</span>
                            <span class="font-bold text-[var(--text-heading)]">{{ selectedAgendamento.forma_pagamento_label }}</span>
                        </div>

                        <div>
                            <span class="text-[10px] font-black text-slate-400 uppercase block">Status do Comprovante</span>
                            <span v-if="selectedAgendamento.comprovante_pix_url" class="font-black text-emerald-600 dark:text-emerald-400 flex items-center gap-1 mt-0.5">
                                <CheckCircleIcon class="w-4 h-4" /> Comprovante Anexado
                            </span>
                            <span v-else class="font-medium text-slate-400">Nenhum arquivo enviado</span>
                        </div>
                    </div>

                    <!-- Botão e Preview de Comprovante PIX -->
                    <div v-if="selectedAgendamento.comprovante_pix_url" class="pt-2 border-t border-emerald-500/20 flex items-center justify-between">
                        <span class="text-[11px] font-medium text-emerald-800 dark:text-emerald-300">Conferir arquivo de comprovante PIX:</span>
                        <a :href="selectedAgendamento.comprovante_pix_url" target="_blank" class="px-3 py-1.5 bg-emerald-500 text-white rounded-xl text-xs font-bold hover:bg-emerald-600 flex items-center gap-1.5 transition-all">
                            <PaperClipIcon class="w-4 h-4" /> Abrir / Baixar Comprovante
                        </a>
                    </div>
                </div>

                <!-- Formulário de Aprovação e Ajuste de Agenda (Disponível apenas se PENDENTE ou CONFIRMADO) -->
                <div v-if="!isRecusando && ['pendente', 'confirmado'].includes(selectedAgendamento.status)" class="space-y-4">
                    <div class="p-4 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-[var(--border-color)] space-y-4">
                        <div class="flex items-center justify-between border-b border-[var(--border-color)] pb-2">
                            <span class="text-xs font-black uppercase tracking-widest text-slate-500 flex items-center gap-2">
                                <CalendarDaysIcon class="w-4 h-4 text-primary-dynamic" /> Validação de Agenda & Data do Atendimento
                            </span>
                            <span class="text-[10px] font-bold text-slate-400">Data Solicitada: {{ new Date(selectedAgendamento.data_hora).toLocaleString('pt-BR') }}</span>
                        </div>

                        <div>
                            <InputLabel value="Confirmar ou Reagendar Data/Hora do Atendimento *" class="!font-black text-[10px] uppercase" />
                            <input 
                                type="datetime-local" 
                                v-model="approveForm.data_hora" 
                                required
                                class="mt-1 block w-full rounded-xl border border-[var(--border-color)] bg-white dark:bg-slate-900 text-xs font-bold p-3 focus:ring-primary-dynamic focus:border-primary-dynamic"
                            />
                            <p class="text-[10px] text-slate-400 mt-1">Caso o horário solicitado pelo cliente não esteja disponível, ajuste a data/hora acima antes de confirmar.</p>
                        </div>

                        <div>
                            <InputLabel value="Orientações e Instruções para o Cliente (opcional)" class="!font-black text-[10px] uppercase" />
                            <textarea 
                                v-model="approveForm.observacoes" 
                                rows="3" 
                                placeholder="Ex: Comparecer ao Consultório 03 com 15 minutos de antecedência e documento com foto."
                                class="mt-1 block w-full rounded-xl border border-[var(--border-color)] bg-white dark:bg-slate-900 text-xs font-medium p-3 resize-none focus:ring-primary-dynamic focus:border-primary-dynamic"
                            ></textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-[var(--border-color)]">
                        <button v-if="selectedAgendamento.status === 'pendente'" type="button" @click="isRecusando = true" class="text-xs font-black text-rose-500 hover:underline">
                            Recusar / Cancelar Agendamento
                        </button>
                        <div v-else></div>

                        <div class="flex gap-2">
                            <SecondaryButton @click="closeModal">Fechar</SecondaryButton>
                            
                            <button 
                                v-if="selectedAgendamento.status === 'confirmado'" 
                                type="button" 
                                @click="realizar(selectedAgendamento.id); closeModal();"
                                class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 flex items-center gap-1.5 transition-all"
                            >
                                ✅ Marcar como Realizado
                            </button>

                            <PrimaryButton @click="submitAprovacao" :disabled="approveForm.processing" class="!bg-blue-600 hover:!bg-blue-700">
                                {{ selectedAgendamento.status === 'confirmado' ? 'Salvar Alterações' : 'Aprovar & Confirmar Agendamento' }}
                            </PrimaryButton>
                        </div>
                    </div>
                </div>

                <!-- Formulário de Recusa -->
                <div v-else-if="isRecusando && selectedAgendamento.status === 'pendente'" class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800/40 rounded-2xl space-y-4">
                    <div class="flex items-center gap-2 text-xs font-black text-rose-600 dark:text-rose-400 uppercase tracking-widest">
                        <ExclamationTriangleIcon class="w-4 h-4" /> Informar Motivo da Recusa / Cancelamento
                    </div>

                    <div>
                        <InputLabel value="Motivo do Cancelamento *" class="!font-black text-[10px] uppercase text-rose-700 dark:text-rose-400" />
                        <textarea 
                            v-model="recusarForm.motivo" 
                            rows="3" 
                            required
                            placeholder="Informe o motivo pelo qual a solicitação não pôde ser atendida..."
                            class="mt-1 block w-full rounded-xl border border-rose-300 dark:border-rose-800 bg-white dark:bg-slate-900 text-xs font-medium p-3 resize-none focus:ring-rose-500 focus:border-rose-500"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <SecondaryButton type="button" @click="isRecusando = false">Voltar</SecondaryButton>
                        <PrimaryButton type="button" @click="submitRecusa" :disabled="recusarForm.processing" class="!bg-rose-600 hover:!bg-rose-700">
                            Confirmar Recusa
                        </PrimaryButton>
                    </div>
                </div>

                <!-- Rodapé para Modo Leitura (Realizado / Cancelado) -->
                <div v-else-if="['realizado', 'cancelado'].includes(selectedAgendamento.status)" class="flex justify-end pt-3 border-t border-[var(--border-color)]">
                    <SecondaryButton @click="closeModal">Fechar Histórico</SecondaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
