<script setup>
import PrestadorLayout from '@/Layouts/PrestadorLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    CalendarDaysIcon,
    ClipboardDocumentListIcon,
    CheckBadgeIcon,
    CheckCircleIcon,
    ClockIcon,
    CurrencyDollarIcon,
    UserIcon,
    BuildingOffice2Icon,
    ChevronLeftIcon,
    ChevronRightIcon,
    MagnifyingGlassIcon,
    XCircleIcon,
    TagIcon,
    SparklesIcon,
    ArrowTopRightOnSquareIcon,
    ChatBubbleBottomCenterTextIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    prestador: Object,
    solicitacoes: Array,
    stats: Object,
    servicos: Array,
});

const page = usePage();
const primaryColor = computed(() => page.props.tenant?.primary_color || '#0d9488');

// Controle de Abas
const abaAtiva = ref('calendario'); // 'calendario' ou 'solicitacoes'

// Modal de Detalhes
const showModalDetalhes = ref(false);
const itemSelecionado = ref(null);

const concluirForm = useForm({
    observacoes: '',
});

const abrirModalDetalhes = (item) => {
    itemSelecionado.value = item;
    concluirForm.observacoes = item.observacoes || '';
    showModalDetalhes.value = true;
};

const confirmarConclusao = () => {
    if (!itemSelecionado.value) return;

    concluirForm.post(route('prestador.solicitacoes.concluir', itemSelecionado.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showModalDetalhes.value = false;
            itemSelecionado.value = null;
        }
    });
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val || 0);
};

const statusConfig = {
    'aprovado_cooperativa': {
        color: 'text-emerald-500',
        bg: 'bg-emerald-50 dark:bg-emerald-500/10',
        border: 'border-emerald-200 dark:border-emerald-500/20',
        badge: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
        icon: CheckBadgeIcon,
        label: 'Autorizado / Agendado'
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
        icon: CheckCircleIcon,
        label: 'Concluído / Faturado'
    },
};

const getStatus = (status) => statusConfig[status] || statusConfig['aprovado_cooperativa'];

// ==========================================
// LÓGICA DO CALENDÁRIO INTERATIVO
// ==========================================
const dataAtual = ref(new Date());
const diaSelecionado = ref(new Date().toISOString().split('T')[0]);

const mesesNomes = [
    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
];

const mesAtualTexto = computed(() => {
    return `${mesesNomes[dataAtual.value.getMonth()]} de ${dataAtual.value.getFullYear()}`;
});

const mesAnterior = () => {
    dataAtual.value = new Date(dataAtual.value.getFullYear(), dataAtual.value.getMonth() - 1, 1);
};

const mesProximo = () => {
    dataAtual.value = new Date(dataAtual.value.getFullYear(), dataAtual.value.getMonth() + 1, 1);
};

const hoje = () => {
    dataAtual.value = new Date();
    diaSelecionado.value = new Date().toISOString().split('T')[0];
};

// Dias do calendário com padding do mês
const diasDoMes = computed(() => {
    const ano = dataAtual.value.getFullYear();
    const mes = dataAtual.value.getMonth();

    const primeiroDiaSemana = new Date(ano, mes, 1).getDay(); // 0 (Domingo) a 6 (Sábado)
    const ultimoDiaMes = new Date(ano, mes + 1, 0).getDate();

    const dias = [];

    // Preenchimento de dias do mês anterior
    const ultimoDiaMesAnterior = new Date(ano, mes, 0).getDate();
    for (let i = primeiroDiaSemana - 1; i >= 0; i--) {
        dias.push({
            dia: ultimoDiaMesAnterior - i,
            mesAtual: false,
            dataStr: '',
            solicitacoes: [],
        });
    }

    // Dias do mês atual
    const hojeStr = new Date().toISOString().split('T')[0];
    for (let d = 1; d <= ultimoDiaMes; d++) {
        const dStr = `${ano}-${String(mes + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        
        // Buscar agendamentos deste dia
        const solsDoDia = props.solicitacoes.filter(s => {
            if (!s.data_agendada) return false;
            // s.data_agendada formatada como 'd/m/Y H:i'
            const partes = s.data_agendada.split(' ')[0].split('/');
            if (partes.length === 3) {
                const isoDate = `${partes[2]}-${partes[1]}-${partes[0]}`;
                return isoDate === dStr;
            }
            return false;
        });

        dias.push({
            dia: d,
            mesAtual: true,
            isHoje: dStr === hojeStr,
            dataStr: dStr,
            solicitacoes: solsDoDia,
        });
    }

    return dias;
});

// Agendamentos do dia selecionado
const agendamentosDiaSelecionado = computed(() => {
    if (!diaSelecionado.value) return [];
    
    return props.solicitacoes.filter(s => {
        if (!s.data_agendada) return false;
        const partes = s.data_agendada.split(' ')[0].split('/');
        if (partes.length === 3) {
            const isoDate = `${partes[2]}-${partes[1]}-${partes[0]}`;
            return isoDate === diaSelecionado.value;
        }
        return false;
    });
});

const selecionarDia = (diaObj) => {
    if (diaObj.dataStr) {
        diaSelecionado.value = diaObj.dataStr;
    }
};

const formatarDataIso = (isoStr) => {
    if (!isoStr) return '';
    const [ano, mes, dia] = isoStr.split('-');
    return `${dia}/${mes}/${ano}`;
};

// ==========================================
// LÓGICA DA ABA DE SOLICITAÇÕES (CARDS + PAGINAÇÃO)
// ==========================================
const buscaTermo = ref('');
const filtroStatusCard = ref('todos');
const paginaAtual = ref(1);
const itensPorPagina = 6;

const solicitacoesFiltradas = computed(() => {
    return props.solicitacoes.filter(s => {
        // Filtro de status
        if (filtroStatusCard.value === 'agendados' && s.status !== 'aprovado_cooperativa') return false;
        if (filtroStatusCard.value === 'concluidos' && s.status !== 'realizado') return false;

        // Filtro de busca
        if (buscaTermo.value.trim() !== '') {
            const termo = buscaTermo.value.toLowerCase();
            const pac = (s.paciente || '').toLowerCase();
            const guia = (s.codigo_guia || '').toLowerCase();
            const srv = (s.servico || '').toLowerCase();
            return pac.includes(termo) || guia.includes(termo) || srv.includes(termo);
        }

        return true;
    });
});

const totalPaginas = computed(() => {
    return Math.ceil(solicitacoesFiltradas.value.length / itensPorPagina) || 1;
});

const solicitacoesPaginadas = computed(() => {
    const inicio = (paginaAtual.value - 1) * itensPorPagina;
    return solicitacoesFiltradas.value.slice(inicio, inicio + itensPorPagina);
});

const mudarPagina = (pag) => {
    if (pag >= 1 && pag <= totalPaginas.value) {
        paginaAtual.value = pag;
    }
};
</script>

<template>
    <Head title="Painel do Parceiro — Central de Atendimentos & Convênios" />

    <PrestadorLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">Central do Parceiro Credenciado</h2>
                    <p class="text-[12px] text-[var(--text-muted)] mt-0.5 font-bold">
                        Gestão simplificada de agendamentos, autorizações digitais e fechamento de convênio.
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
                            <p class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Total de Pedidos</p>
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
                            <p class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Concluídos neste Mês</p>
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

            <!-- Navegação por Abas (Tabs) -->
            <div class="border-b border-[var(--border-color)]">
                <nav class="flex space-x-6" aria-label="Tabs">
                    <button
                        @click="abaAtiva = 'calendario'"
                        :class="[
                            'pb-4 px-1 border-b-2 font-black text-sm flex items-center gap-2 transition-all',
                            abaAtiva === 'calendario'
                                ? 'border-primary-dynamic text-primary-dynamic'
                                : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'
                        ]"
                    >
                        <CalendarDaysIcon class="w-5 h-5" />
                        Agenda & Calendário de Compromissos
                        <span v-if="stats?.agendadas > 0" class="ml-1.5 py-0.5 px-2 rounded-full text-[10px] bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                            {{ stats.agendadas }}
                        </span>
                    </button>

                    <button
                        @click="abaAtiva = 'solicitacoes'"
                        :class="[
                            'pb-4 px-1 border-b-2 font-black text-sm flex items-center gap-2 transition-all',
                            abaAtiva === 'solicitacoes'
                                ? 'border-primary-dynamic text-primary-dynamic'
                                : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'
                        ]"
                    >
                        <ClipboardDocumentListIcon class="w-5 h-5" />
                        Solicitações & Histórico (Cards)
                        <span class="ml-1.5 py-0.5 px-2 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                            {{ solicitacoes.length }}
                        </span>
                    </button>
                </nav>
            </div>

            <!-- ============================================================== -->
            <!-- ABA 1: AGENDA & CALENDÁRIO INTERATIVO                          -->
            <!-- ============================================================== -->
            <div v-show="abaAtiva === 'calendario'" class="space-y-6">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    
                    <!-- Calendário Mensal -->
                    <div class="lg:col-span-8 zynix-card p-6">
                        
                        <!-- Header do Calendário -->
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-[var(--border-color)]">
                            <div>
                                <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight capitalize">
                                    {{ mesAtualTexto }}
                                </h3>
                                <p class="text-xs text-[var(--text-muted)] font-medium mt-0.5">
                                    Clique em qualquer dia para ver os agendamentos ou no evento para detalhes.
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button @click="hoje" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-black uppercase tracking-wider transition-colors">
                                    Hoje
                                </button>
                                <button @click="mesAnterior" class="p-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 rounded-lg transition-colors">
                                    <ChevronLeftIcon class="w-4 h-4" />
                                </button>
                                <button @click="mesProximo" class="p-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 rounded-lg transition-colors">
                                    <ChevronRightIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <!-- Dias da Semana -->
                        <div class="grid grid-cols-7 gap-1 text-center mb-2">
                            <span v-for="sem in ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb']" :key="sem" class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] py-1">
                                {{ sem }}
                            </span>
                        </div>

                        <!-- Grid de Dias do Mês -->
                        <div class="grid grid-cols-7 gap-1.5">
                            <div 
                                v-for="(diaObj, idx) in diasDoMes" 
                                :key="idx"
                                @click="selecionarDia(diaObj)"
                                :class="[
                                    'min-h-[75px] p-2 rounded-xl border transition-all text-left flex flex-col justify-between cursor-pointer',
                                    !diaObj.mesAtual ? 'opacity-30 bg-slate-50 dark:bg-slate-900 border-transparent' : 'border-[var(--border-color)] bg-white dark:bg-slate-900/60 hover:border-primary-dynamic/50',
                                    diaObj.dataStr === diaSelecionado ? 'ring-2 ring-primary-dynamic border-primary-dynamic shadow-sm' : '',
                                    diaObj.isHoje ? 'bg-primary-dynamic/5' : ''
                                ]"
                            >
                                <div class="flex items-center justify-between">
                                    <span :class="['text-xs font-black', diaObj.isHoje ? 'text-primary-dynamic font-extrabold underline' : 'text-slate-700 dark:text-slate-300']">
                                        {{ diaObj.dia }}
                                    </span>
                                    <span v-if="diaObj.solicitacoes?.length > 0" class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                </div>

                                <!-- Eventos / Compromissos no Dia -->
                                <div v-if="diaObj.solicitacoes?.length > 0" class="mt-1 space-y-1">
                                    <div 
                                        v-for="sol in diaObj.solicitacoes.slice(0, 2)" 
                                        :key="sol.id"
                                        @click.stop="abrirModalDetalhes(sol)"
                                        :class="['text-[9px] font-black truncate px-1.5 py-0.5 rounded shadow-xs cursor-pointer hover:opacity-80 transition-opacity', sol.status === 'realizado' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800']"
                                        :title="`${sol.paciente} - ${sol.servico}`"
                                    >
                                        {{ sol.paciente.split(' ')[0] }} ({{ sol.servico.substring(0, 8) }}...)
                                    </div>
                                    <span v-if="diaObj.solicitacoes.length > 2" class="text-[8px] font-bold text-slate-400 block text-right">
                                        +{{ diaObj.solicitacoes.length - 2 }} mais
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Coluna Lateral: Detalhes do Dia Selecionado -->
                    <div class="lg:col-span-4 zynix-card p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between pb-3 border-b border-[var(--border-color)] mb-4">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Compromissos para</span>
                                    <h4 class="text-base font-black text-[var(--text-heading)]">
                                        {{ formatarDataIso(diaSelecionado) }}
                                    </h4>
                                </div>
                                <span class="text-xs font-black px-2 py-1 bg-primary-dynamic/10 text-primary-dynamic rounded-lg">
                                    {{ agendamentosDiaSelecionado.length }} agendados
                                </span>
                            </div>

                            <div v-if="agendamentosDiaSelecionado.length > 0" class="space-y-3 max-h-[380px] overflow-y-auto pr-1">
                                <div 
                                    v-for="sol in agendamentosDiaSelecionado" 
                                    :key="sol.id"
                                    @click="abrirModalDetalhes(sol)"
                                    class="p-3.5 rounded-xl border border-[var(--border-color)] bg-slate-50/50 dark:bg-slate-800/40 hover:border-primary-dynamic/40 cursor-pointer transition-all hover:shadow-xs group"
                                >
                                    <div class="flex items-start justify-between gap-2 mb-1.5">
                                        <span class="font-mono text-[10px] font-bold text-primary-dynamic">{{ sol.codigo_guia }}</span>
                                        <span :class="['text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full', getStatus(sol.status).badge]">
                                            {{ getStatus(sol.status).label }}
                                        </span>
                                    </div>

                                    <h5 class="text-xs font-black text-[var(--text-heading)] group-hover:text-primary-dynamic transition-colors">
                                        {{ sol.paciente }}
                                    </h5>
                                    <p class="text-[11px] text-slate-600 dark:text-slate-400 font-medium line-clamp-1">
                                        {{ sol.servico }}
                                    </p>

                                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-[var(--border-color)]/60 text-[10px]">
                                        <span class="text-slate-400 font-bold flex items-center gap-1">
                                            <ClockIcon class="w-3 h-3 text-slate-400" />
                                            {{ sol.data_agendada.split(' ')[1] || 'Horário marcado' }}
                                        </span>
                                        <span class="font-black text-slate-700 dark:text-slate-300">
                                            {{ formatCurrency(sol.valor_cobrado) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-center py-12 text-slate-400">
                                <CalendarDaysIcon class="w-10 h-10 mx-auto text-slate-300 mb-2" />
                                <p class="text-xs font-bold">Nenhum compromisso para este dia.</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">Selecione outro dia no calendário ao lado.</p>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-[var(--border-color)] text-center">
                            <p class="text-[10px] text-slate-400">
                                Clique em qualquer item para ver detalhes completos e validar atendimento.
                            </p>
                        </div>
                    </div>

                </div>

            </div>

            <!-- ============================================================== -->
            <!-- ABA 2: SOLICITAÇÕES & HISTÓRICO (CARDS COM PAGINAÇÃO)         -->
            <!-- ============================================================== -->
            <div v-show="abaAtiva === 'solicitacoes'" class="space-y-6">
                
                <div class="zynix-card p-6">
                    
                    <!-- Barra de Ferramentas / Filtros -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                        
                        <!-- Busca Rápida -->
                        <div class="relative w-full md:w-80">
                            <MagnifyingGlassIcon class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
                            <input 
                                type="text"
                                v-model="buscaTermo"
                                @input="paginaAtual = 1"
                                placeholder="Buscar por Paciente, Guia ou Serviço..."
                                class="w-full pl-9 text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-primary-dynamic font-medium"
                            />
                        </div>

                        <!-- Filtros de Status -->
                        <div class="flex items-center gap-2">
                            <button 
                                @click="() => { filtroStatusCard = 'todos'; paginaAtual = 1; }"
                                :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-all', filtroStatusCard === 'todos' ? 'bg-primary-dynamic text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200']"
                            >
                                Todos ({{ solicitacoes.length }})
                            </button>
                            <button 
                                @click="() => { filtroStatusCard = 'agendados'; paginaAtual = 1; }"
                                :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-all', filtroStatusCard === 'agendados' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200']"
                            >
                                Agendados ({{ stats?.agendadas || 0 }})
                            </button>
                            <button 
                                @click="() => { filtroStatusCard = 'concluidos'; paginaAtual = 1; }"
                                :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-all', filtroStatusCard === 'concluidos' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200']"
                            >
                                Concluídos ({{ stats?.realizadas || 0 }})
                            </button>
                        </div>

                    </div>

                    <!-- Grid de Cards Paginados -->
                    <div v-if="solicitacoesPaginadas.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div 
                            v-for="sol in solicitacoesPaginadas" 
                            :key="sol.id"
                            class="zynix-card p-5 border-l-4 transition-all hover:shadow-md flex flex-col justify-between"
                            :class="getStatus(sol.status).border"
                        >
                            <div>
                                <!-- Topo do Card -->
                                <div class="flex items-start justify-between gap-2 mb-3">
                                    <span class="font-mono text-xs font-black text-primary-dynamic">{{ sol.codigo_guia }}</span>
                                    <span :class="['text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full', getStatus(sol.status).badge]">
                                        {{ getStatus(sol.status).label }}
                                    </span>
                                </div>

                                <!-- Paciente / Beneficiário -->
                                <h4 class="text-sm font-black text-[var(--text-heading)] line-clamp-1">
                                    {{ sol.paciente }}
                                </h4>
                                <p class="text-[10px] text-[var(--text-muted)] font-bold uppercase tracking-wider mt-0.5">
                                    Titular: {{ sol.titular_nome }} • Mat: {{ sol.matricula }}
                                </p>

                                <!-- Procedimento -->
                                <div class="mt-3 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 block">Item / Procedimento</span>
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-200 line-clamp-1 mt-0.5">
                                        {{ sol.servico }}
                                    </p>
                                </div>
                            </div>

                            <!-- Rodapé do Card com Ação -->
                            <div class="mt-4 pt-3 border-t border-[var(--border-color)] flex items-center justify-between">
                                <div>
                                    <span class="text-[9px] font-bold text-slate-400 block">Valor Acordado</span>
                                    <span class="text-xs font-black text-slate-800 dark:text-slate-200">{{ formatCurrency(sol.valor_cobrado) }}</span>
                                </div>

                                <button 
                                    @click="abrirModalDetalhes(sol)"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-primary-dynamic hover:text-white text-slate-700 dark:text-slate-300 rounded-lg text-xs font-black uppercase tracking-wider transition-all"
                                >
                                    Ver Detalhes
                                    <ArrowTopRightOnSquareIcon class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Estado Vazio -->
                    <div v-else class="text-center py-16 border border-dashed border-[var(--border-color)] rounded-xl">
                        <ClipboardDocumentListIcon class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                        <h4 class="text-base font-black text-[var(--text-heading)]">Nenhum registro encontrado</h4>
                        <p class="text-xs text-[var(--text-muted)] max-w-sm mx-auto mt-1">
                            Não encontramos solicitações com os filtros ou termo pesquisado.
                        </p>
                    </div>

                    <!-- Controles de Paginação -->
                    <div v-if="totalPaginas > 1" class="flex items-center justify-between mt-6 pt-4 border-t border-[var(--border-color)]">
                        <span class="text-xs font-bold text-[var(--text-muted)]">
                            Página {{ paginaAtual }} de {{ totalPaginas }} ({{ solicitacoesFiltradas.length }} registros)
                        </span>

                        <div class="flex items-center gap-2">
                            <button 
                                @click="mudarPagina(paginaAtual - 1)" 
                                :disabled="paginaAtual === 1"
                                class="px-3 py-1.5 rounded-lg border border-[var(--border-color)] text-xs font-bold disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                            >
                                Anterior
                            </button>
                            <button 
                                @click="mudarPagina(paginaAtual + 1)" 
                                :disabled="paginaAtual === totalPaginas"
                                class="px-3 py-1.5 rounded-lg border border-[var(--border-color)] text-xs font-bold disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                            >
                                Próxima
                            </button>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <!-- ============================================================== -->
        <!-- MODAL CENTRALIZADO DE DETALHES & AÇÕES DO PARCEIRO             -->
        <!-- ============================================================== -->
        <div v-if="showModalDetalhes" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-[var(--border-color)] animate-in fade-in zoom-in-95 max-h-[90vh] overflow-y-auto">
                
                <!-- Cabeçalho do Modal -->
                <div class="flex items-center justify-between border-b border-[var(--border-color)] pb-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div :class="['w-10 h-10 rounded-xl flex items-center justify-center', getStatus(itemSelecionado?.status).bg, getStatus(itemSelecionado?.status).color]">
                            <component :is="getStatus(itemSelecionado?.status).icon" class="w-6 h-6" />
                        </div>
                        <div>
                            <span class="font-mono text-xs font-black text-primary-dynamic block">{{ itemSelecionado?.codigo_guia }}</span>
                            <h3 class="text-base font-black text-[var(--text-heading)] tracking-tight">Detalhes do Atendimento / Pedido</h3>
                        </div>
                    </div>
                    <button @click="showModalDetalhes = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <XCircleIcon class="w-6 h-6" />
                    </button>
                </div>

                <!-- Conteúdo dos Dados -->
                <div class="space-y-4 text-xs">
                    
                    <!-- Status Atual -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-500">Situação da Guia:</span>
                        <span :class="['px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider', getStatus(itemSelecionado?.status).badge]">
                            {{ getStatus(itemSelecionado?.status).label }}
                        </span>
                    </div>

                    <!-- Dados do Beneficiário / Cliente -->
                    <div class="p-3.5 rounded-xl border border-[var(--border-color)] space-y-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] block">Dados do Beneficiário</span>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-slate-400 block text-[10px]">Nome do Paciente / Cliente:</span>
                                <strong class="text-slate-800 dark:text-slate-200 text-xs">{{ itemSelecionado?.paciente }}</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px]">Cooperado Titular:</span>
                                <strong class="text-slate-800 dark:text-slate-200 text-xs">{{ itemSelecionado?.titular_nome }}</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px]">Matrícula Societária:</span>
                                <strong class="text-slate-800 dark:text-slate-200 text-xs">{{ itemSelecionado?.matricula }}</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px]">Data do Pedido:</span>
                                <strong class="text-slate-800 dark:text-slate-200 text-xs">{{ itemSelecionado?.data }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Procedimento e Valores -->
                    <div class="p-3.5 rounded-xl border border-[var(--border-color)] space-y-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] block">Serviço / Produto Autorizado</span>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-700 dark:text-slate-200 text-sm">{{ itemSelecionado?.servico }}</span>
                            <span class="text-sm font-black text-emerald-600">{{ formatCurrency(itemSelecionado?.valor_cobrado) }}</span>
                        </div>
                        <div v-if="itemSelecionado?.data_agendada" class="text-slate-500 font-medium pt-1 flex items-center gap-1.5">
                            <CalendarDaysIcon class="w-4 h-4 text-primary-dynamic" />
                            <span>Agendado para: <strong>{{ itemSelecionado.data_agendada }}</strong></span>
                        </div>
                    </div>

                    <!-- Observação do Cooperado -->
                    <div v-if="itemSelecionado?.observacoes" class="p-3 bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/40 rounded-xl">
                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-800 dark:text-amber-400 block">Mensagem do Solicitante:</span>
                        <p class="text-slate-700 dark:text-slate-300 italic mt-0.5 font-medium">"{{ itemSelecionado.observacoes }}"</p>
                    </div>

                    <!-- Registro de Conclusão / Laudo (Apenas se ainda pendente de conclusão) -->
                    <div v-if="itemSelecionado?.status === 'aprovado_cooperativa'" class="pt-2">
                        <label class="block text-xs font-bold text-[var(--text-heading)] mb-1">
                            Observações do Parceiro / Laudo / Nº do Cupom Fiscal (Opcional):
                        </label>
                        <textarea 
                            v-model="concluirForm.observacoes"
                            rows="2" 
                            class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-emerald-500 focus:ring focus:ring-emerald-200 font-medium"
                            placeholder="Ex: Consulta realizada com prescrição médica. OU Cupom fiscal nº 1092 emitido."
                        ></textarea>
                    </div>

                    <div v-else-if="itemSelecionado?.status === 'realizado'" class="p-3 bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-900/40 rounded-xl">
                        <span class="text-[10px] font-black uppercase tracking-wider text-blue-800 dark:text-blue-400 block">Atendimento Realizado</span>
                        <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5">
                            Este atendimento foi finalizado em <strong>{{ itemSelecionado.data_realizacao || itemSelecionado.data }}</strong> e já está computado na fatura mensal do dia 05.
                        </p>
                    </div>

                </div>

                <!-- Rodapé de Ações do Modal -->
                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-[var(--border-color)]">
                    <button 
                        @click="showModalDetalhes = false" 
                        class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                    >
                        Fechar
                    </button>
                    
                    <button 
                        v-if="itemSelecionado?.status === 'aprovado_cooperativa'"
                        @click="confirmarConclusao"
                        :disabled="concluirForm.processing"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-sm transition-all flex items-center gap-2"
                    >
                        <CheckCircleIcon class="w-4 h-4" />
                        Concluir e Faturar Atendimento
                    </button>
                </div>

            </div>
        </div>

    </PrestadorLayout>
</template>

<style scoped>
.text-primary-dynamic { color: v-bind(primaryColor); }
.bg-primary-dynamic { background-color: v-bind(primaryColor) !important; }
.border-primary-dynamic { border-color: v-bind(primaryColor) !important; }
</style>
