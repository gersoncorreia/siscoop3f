<script setup>
import PrestadorLayout from '@/Layouts/PrestadorLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    Squares2X2Icon,
    CheckCircleIcon,
    ClockIcon,
    ClipboardDocumentListIcon,
    CalendarDaysIcon,
    CurrencyDollarIcon,
    UserIcon,
    BuildingOffice2Icon,
    ArrowPathIcon,
    CheckBadgeIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    prestador: Object,
    solicitacoes: Array,
    stats: Object,
    servicos: Array,
});

const page = usePage();
const primaryColor = computed(() => page.props.tenant?.primary_color || '#0d9488');

const concluirForm = useForm({
    observacoes: '',
});

const selectedSolicitacao = ref(null);
const showConcluirModal = ref(false);

const abrirModalConcluir = (sol) => {
    selectedSolicitacao.value = sol;
    concluirForm.observacoes = sol.observacoes || '';
    showConcluirModal.value = true;
};

const confirmarConclusao = () => {
    if (!selectedSolicitacao.value) return;

    concluirForm.post(route('prestador.solicitacoes.concluir', selectedSolicitacao.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showConcluirModal.value = false;
            selectedSolicitacao.value = null;
        }
    });
};

const statusConfig = {
    'aprovado_cooperativa': {
        color: 'text-emerald-500',
        bg: 'bg-emerald-50 dark:bg-emerald-500/10',
        border: 'border-emerald-200 dark:border-emerald-500/20',
        badge: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
        icon: CheckBadgeIcon,
        label: 'Autorizado & Agendado'
    },
    'pendente_autorizacao': {
        color: 'text-amber-500',
        bg: 'bg-amber-50 dark:bg-amber-500/10',
        border: 'border-amber-200 dark:border-amber-500/20',
        badge: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
        icon: ClockIcon,
        label: 'Em Processamento'
    },
    'realizado': {
        color: 'text-blue-500',
        bg: 'bg-blue-50 dark:bg-blue-500/10',
        border: 'border-blue-200 dark:border-blue-500/20',
        badge: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        icon: ClipboardDocumentListIcon,
        label: 'Atendimento Concluído'
    },
};

const getStatus = (status) => statusConfig[status] || statusConfig['aprovado_cooperativa'];

const formatCurrency = (val) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val || 0);
};

// Filtro de solicitações
const filtroStatus = ref('todos');
const solicitacoesFiltradas = computed(() => {
    if (filtroStatus.value === 'todos') return props.solicitacoes;
    if (filtroStatus.value === 'agendados') return props.solicitacoes.filter(s => s.status === 'aprovado_cooperativa');
    if (filtroStatus.value === 'realizados') return props.solicitacoes.filter(s => s.status === 'realizado');
    return props.solicitacoes;
});
</script>

<template>
    <Head title="Painel do Parceiro — Central de Atendimentos Online" />

    <PrestadorLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">Central de Agendamentos & Atendimentos</h2>
                    <p class="text-[12px] text-[var(--text-muted)] mt-0.5 font-bold">
                        Fluxo 100% digital: visualize as solicitações agendadas pelos cooperados com autorização garantida.
                    </p>
                </div>
                <div class="p-3 bg-[var(--bg-card)] rounded-xl border border-[var(--border-color)]">
                    <BuildingOffice2Icon class="w-6 h-6 text-primary-dynamic" />
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12">

            <!-- Flash Messages -->
            <div v-if="$page.props.flash?.success" class="bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 p-4 rounded-xl border border-emerald-200 dark:border-emerald-800 flex items-center shadow-sm">
                <CheckCircleIcon class="w-5 h-5 mr-3 flex-shrink-0" />
                <span class="text-sm font-bold">{{ $page.props.flash.success }}</span>
            </div>

            <!-- Cards de Métricas em Tempo Real -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <div class="zynix-card p-5 border-l-4 border-l-primary-dynamic">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Total de Solicitações</p>
                            <p class="text-2xl font-black text-[var(--text-heading)] mt-1">{{ stats?.total_recebidas || solicitacoes.length }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-primary-dynamic/10 text-primary-dynamic flex items-center justify-center">
                            <ClipboardDocumentListIcon class="w-5 h-5" />
                        </div>
                    </div>
                </div>

                <div class="zynix-card p-5 border-l-4 border-l-emerald-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Agendados & Aprovados</p>
                            <p class="text-2xl font-black text-emerald-600 mt-1">{{ stats?.agendadas || 0 }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                            <CalendarDaysIcon class="w-5 h-5" />
                        </div>
                    </div>
                </div>

                <div class="zynix-card p-5 border-l-4 border-l-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Atendidos neste Mês</p>
                            <p class="text-2xl font-black text-blue-600 mt-1">{{ stats?.realizadas || 0 }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-500 flex items-center justify-center">
                            <CheckCircleIcon class="w-5 h-5" />
                        </div>
                    </div>
                </div>

                <div class="zynix-card p-5 border-l-4 border-l-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Fatura Prevista (Dia 05)</p>
                            <p class="text-2xl font-black text-purple-600 mt-1">{{ formatCurrency(stats?.total_faturado_mes || 0) }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-500 flex items-center justify-center">
                            <CurrencyDollarIcon class="w-5 h-5" />
                        </div>
                    </div>
                </div>

            </div>

            <!-- Central de Agendamentos e Solicitações Recebidas -->
            <div class="zynix-card p-6">
                
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight flex items-center gap-2">
                            <CalendarDaysIcon class="w-5 h-5 text-primary-dynamic" />
                            Agendamentos & Solicitações Recebidas
                        </h3>
                        <p class="text-xs text-[var(--text-muted)] font-medium mt-0.5">
                            Pacientes elegíveis com autorização gerada automaticamente pelo sistema da cooperativa.
                        </p>
                    </div>

                    <!-- Filtros Rápidos -->
                    <div class="flex items-center gap-2">
                        <button 
                            @click="filtroStatus = 'todos'" 
                            :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-all', filtroStatus === 'todos' ? 'bg-primary-dynamic text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200']"
                        >
                            Todos ({{ solicitacoes.length }})
                        </button>
                        <button 
                            @click="filtroStatus = 'agendados'" 
                            :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-all', filtroStatus === 'agendados' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200']"
                        >
                            Agendados ({{ stats?.agendadas || 0 }})
                        </button>
                        <button 
                            @click="filtroStatus = 'realizados'" 
                            :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-all', filtroStatus === 'realizados' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200']"
                        >
                            Concluídos ({{ stats?.realizadas || 0 }})
                        </button>
                    </div>
                </div>

                <!-- Tabela de Solicitações Recebidas -->
                <div v-if="solicitacoesFiltradas.length > 0" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[var(--border-color)] text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] bg-slate-50/50 dark:bg-slate-800/30">
                                <th class="py-3 px-4">Guia / Código</th>
                                <th class="py-3 px-4">Paciente / Beneficiário</th>
                                <th class="py-3 px-4">Procedimento / Especialidade</th>
                                <th class="py-3 px-4">Data Agendada</th>
                                <th class="py-3 px-4">Valor Acordado</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border-color)] text-sm">
                            <tr v-for="sol in solicitacoesFiltradas" :key="sol.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                
                                <td class="py-4 px-4 font-mono font-bold text-xs text-primary-dynamic">
                                    {{ sol.codigo_guia }}
                                </td>

                                <td class="py-4 px-4">
                                    <div class="font-bold text-[var(--text-heading)]">{{ sol.paciente }}</div>
                                    <div class="text-[10px] text-[var(--text-muted)] font-bold uppercase tracking-wider">
                                        Titular: {{ sol.titular_nome }} • Mat: {{ sol.matricula }}
                                    </div>
                                </td>

                                <td class="py-4 px-4">
                                    <div class="font-semibold text-slate-700 dark:text-slate-200">{{ sol.servico }}</div>
                                    <div v-if="sol.observacoes" class="text-xs text-slate-400 italic line-clamp-1 mt-0.5">
                                        "{{ sol.observacoes }}"
                                    </div>
                                </td>

                                <td class="py-4 px-4">
                                    <div v-if="sol.data_agendada" class="font-black text-slate-800 dark:text-slate-200">
                                        {{ sol.data_agendada }}
                                    </div>
                                    <div v-else class="text-xs text-slate-400 font-medium">
                                        A definir
                                    </div>
                                </td>

                                <td class="py-4 px-4 font-black text-slate-700 dark:text-slate-200">
                                    {{ formatCurrency(sol.valor_cobrado) }}
                                </td>

                                <td class="py-4 px-4">
                                    <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider', getStatus(sol.status).badge]">
                                        <component :is="getStatus(sol.status).icon" class="w-3.5 h-3.5" />
                                        {{ getStatus(sol.status).label }}
                                    </span>
                                </td>

                                <td class="py-4 px-4 text-right">
                                    <button 
                                        v-if="sol.status === 'aprovado_cooperativa'"
                                        @click="abrirModalConcluir(sol)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-black uppercase tracking-wider shadow-sm transition-all"
                                    >
                                        <CheckCircleIcon class="w-4 h-4" />
                                        Concluir Atendimento
                                    </button>
                                    <span v-else-if="sol.status === 'realizado'" class="text-xs font-bold text-slate-400">
                                        Atendido em {{ sol.data_realizacao || sol.data }}
                                    </span>
                                </td>

                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-center py-12 border border-dashed border-[var(--border-color)] rounded-xl mt-4">
                    <CalendarDaysIcon class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                    <h4 class="text-base font-black text-[var(--text-heading)]">Nenhum agendamento encontrado</h4>
                    <p class="text-xs text-[var(--text-muted)] max-w-sm mx-auto mt-1">
                        Assim que os cooperados realizarem solicitações online pelo aplicativo, elas aparecerão aqui automaticamente pré-autorizadas.
                    </p>
                </div>

            </div>

        </div>

        <!-- Modal de Confirmação de Atendimento Concluído -->
        <div v-if="showConcluirModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-[var(--border-color)] animate-in fade-in zoom-in-95">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                        <CheckCircleIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">Confirmar Atendimento</h3>
                        <p class="text-xs text-[var(--text-muted)]">Guia: {{ selectedSolicitacao?.codigo_guia }}</p>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl mb-4 space-y-1.5 text-xs">
                    <p><strong class="text-slate-700 dark:text-slate-300">Paciente:</strong> {{ selectedSolicitacao?.paciente }}</p>
                    <p><strong class="text-slate-700 dark:text-slate-300">Procedimento:</strong> {{ selectedSolicitacao?.servico }}</p>
                    <p><strong class="text-slate-700 dark:text-slate-300">Valor Acordado:</strong> {{ formatCurrency(selectedSolicitacao?.valor_cobrado) }}</p>
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-bold text-[var(--text-heading)] mb-1">
                        Observações Clínicas / Laudo do Atendimento (Opcional):
                    </label>
                    <textarea 
                        v-model="concluirForm.observacoes"
                        rows="3" 
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-emerald-500 focus:ring focus:ring-emerald-200"
                        placeholder="Ex: Consulta realizada sem intercorrências. Prescrição entregue ao paciente."
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button 
                        @click="showConcluirModal = false" 
                        class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                    >
                        Cancelar
                    </button>
                    <button 
                        @click="confirmarConclusao"
                        :disabled="concluirForm.processing"
                        class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black uppercase tracking-wider rounded-lg shadow-sm transition-all"
                    >
                        Confirmar e Lançar Fatura
                    </button>
                </div>
            </div>
        </div>

    </PrestadorLayout>
</template>
