<script setup>
import CooperadoLayout from '@/Layouts/CooperadoLayout.vue';
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import {
    BuildingOffice2Icon,
    MapPinIcon,
    TagIcon,
    ClockIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClipboardDocumentListIcon,
    ArrowLeftIcon,
    CurrencyDollarIcon,
    ChatBubbleBottomCenterTextIcon,
    ExclamationTriangleIcon,
    SparklesIcon,
    CalendarDaysIcon,
    UserIcon,
    CheckBadgeIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    prestador: Object,
    servicos: Array,
    dependentes: Array,
    solicitacoes: Array,
});

const page = usePage();
const primaryColor = computed(() => page.props.tenant?.primary_color || '#0d9488');

const tipoLabel = (tipo) => {
    const map = {
        'clinica': 'Clínica',
        'hospital': 'Hospital',
        'medico_pf': 'Médico(a)',
        'laboratorio': 'Laboratório',
        'farmacia': 'Farmácia',
        'otica': 'Ótica',
    };
    return map[tipo] || tipo;
};

const statusConfig = {
    'aprovado_cooperativa': { color: 'text-emerald-500', bg: 'bg-emerald-50 dark:bg-emerald-500/10', border: 'border-emerald-200 dark:border-emerald-500/20', icon: CheckBadgeIcon, label: 'Autorizado & Agendado' },
    'pendente_autorizacao': { color: 'text-amber-500', bg: 'bg-amber-50 dark:bg-amber-500/10', border: 'border-amber-200 dark:border-amber-500/20', icon: ClockIcon, label: 'Em Processamento' },
    'realizado': { color: 'text-blue-500', bg: 'bg-blue-50 dark:bg-blue-500/10', border: 'border-blue-200 dark:border-blue-500/20', icon: ClipboardDocumentListIcon, label: 'Atendimento Concluído' },
    'negado': { color: 'text-rose-500', bg: 'bg-rose-50 dark:bg-rose-500/10', border: 'border-rose-200 dark:border-rose-500/20', icon: XCircleIcon, label: 'Negado' },
};

const getStatus = (status) => statusConfig[status] || statusConfig['aprovado_cooperativa'];

const formatCurrency = (val) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val || 0);
};

// Modal de Agendamento Online
const showModalAgendar = ref(false);
const servicoSelecionado = ref(null);
const checandoElegibilidade = ref(false);
const elegibilidadeResultado = ref(null);

const formAgendamento = useForm({
    servico_id: '',
    dependente_id: '',
    data_agendada: '',
    turno: 'manha',
    observacoes: '',
});

// Data mínima para agendamento (hoje)
const minDate = new Date().toISOString().split('T')[0];

const abrirModalAgendamento = async (servico) => {
    servicoSelecionado.value = servico;
    formAgendamento.servico_id = servico.id;
    formAgendamento.dependente_id = '';
    formAgendamento.observacoes = '';
    
    // Sugerir dia seguinte
    const amanha = new Date();
    amanha.setDate(amanha.getDate() + 1);
    formAgendamento.data_agendada = amanha.toISOString().split('T')[0];

    showModalAgendar.value = true;
    await verificarElegibilidade();
};

const verificarElegibilidade = async () => {
    if (!servicoSelecionado.value) return;

    checandoElegibilidade.value = true;
    elegibilidadeResultado.value = null;

    try {
        const url = route('cooperado.prestadores.elegibilidade', {
            id: props.prestador.id,
            servicoId: servicoSelecionado.value.id,
            dependente_id: formAgendamento.dependente_id || null,
        });
        const res = await axios.get(url);
        elegibilidadeResultado.value = res.data;
    } catch (e) {
        console.error('Erro na checagem de elegibilidade', e);
        elegibilidadeResultado.value = {
            apto: false,
            motivo: 'Não foi possível consultar os requisitos no momento. Tente novamente.',
        };
    } finally {
        checandoElegibilidade.value = false;
    }
};

const submeterAgendamento = () => {
    formAgendamento.post(route('cooperado.prestadores.solicitar-agendar', props.prestador.id), {
        preserveScroll: true,
        onSuccess: () => {
            showModalAgendar.value = false;
            servicoSelecionado.value = null;
        }
    });
};
</script>

<template>
    <Head :title="`Detalhes - ${prestador.nome}`" />

    <CooperadoLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <Link :href="route('cooperado.prestadores')" class="inline-flex items-center gap-1.5 text-[10px] font-black text-primary-dynamic uppercase tracking-widest mb-3 hover:opacity-70 transition-opacity">
                        <ArrowLeftIcon class="w-3 h-3" />
                        Voltar para Rede Credenciada
                    </Link>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">{{ prestador.nome }}</h2>
                    <p class="text-[12px] text-[var(--text-muted)] mt-0.5 font-bold">
                        Agendamento 100% online com autorização e elegibilidade verificada em tempo real.
                    </p>
                </div>
                <div class="p-3 bg-[var(--bg-card)] rounded-xl border border-[var(--border-color)]">
                    <BuildingOffice2Icon class="w-6 h-6 text-primary-dynamic" />
                </div>
            </div>
        </template>

        <div class="space-y-8 pb-12">

            <!-- Flash Messages -->
            <div v-if="$page.props.flash?.success" class="bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 p-4 rounded-xl border border-emerald-200 dark:border-emerald-800 flex items-center shadow-sm">
                <CheckCircleIcon class="w-5 h-5 mr-3 flex-shrink-0" />
                <span class="text-sm font-bold">{{ $page.props.flash.success }}</span>
            </div>

            <div v-if="$page.props.flash?.error" class="bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-300 p-4 rounded-xl border border-rose-200 dark:border-rose-800 flex items-center shadow-sm">
                <ExclamationTriangleIcon class="w-5 h-5 mr-3 flex-shrink-0" />
                <span class="text-sm font-bold">{{ $page.props.flash.error }}</span>
            </div>

            <!-- Informações do Prestador -->
            <div class="zynix-card p-6">
                <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                    <div class="w-16 h-16 rounded-2xl bg-primary-dynamic/10 text-primary-dynamic flex items-center justify-center shrink-0">
                        <BuildingOffice2Icon class="w-8 h-8" />
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                            <h3 class="text-xl font-black text-[var(--text-heading)] tracking-tight">{{ prestador.nome }}</h3>
                            <span class="text-[9px] font-black uppercase tracking-widest bg-slate-100 dark:bg-slate-800 text-slate-500 px-2.5 py-1 rounded-full">
                                {{ tipoLabel(prestador.tipo) }}
                            </span>
                        </div>
                        <div v-if="prestador.endereco" class="flex items-center text-sm text-[var(--text-muted)] font-medium mb-2">
                            <MapPinIcon class="w-4 h-4 mr-2 text-slate-400 shrink-0" />
                            {{ prestador.endereco }}
                        </div>
                        <div v-if="prestador.especialidades.length > 0" class="flex flex-wrap gap-2 mt-3">
                            <span v-for="esp in prestador.especialidades" :key="esp" class="text-[9px] font-black uppercase tracking-widest bg-primary-dynamic/10 text-primary-dynamic px-2.5 py-1 rounded-full">
                                {{ esp }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Serviços e Agendamento Online -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <SparklesIcon class="w-5 h-5 text-primary-dynamic" />
                        <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">Serviços Disponíveis para Agendamento Online</h3>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-1 rounded-full border border-emerald-200">
                        ⚡ Autorização Instantânea
                    </span>
                </div>

                <div v-if="servicos.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="srv in servicos" :key="srv.id" class="zynix-card p-5 hover:border-primary-dynamic/30 transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-primary-dynamic/10 text-primary-dynamic flex items-center justify-center shrink-0">
                                        <TagIcon class="w-4 h-4" />
                                    </div>
                                    <h4 class="text-sm font-black text-[var(--text-heading)] tracking-tight">{{ srv.nome }}</h4>
                                </div>
                            </div>
                            <p v-if="srv.descricao" class="text-xs text-[var(--text-muted)] font-medium mt-2 line-clamp-2">{{ srv.descricao }}</p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-[var(--border-color)] flex items-center justify-between gap-3">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Valor Acordado</span>
                                <span class="text-sm font-black text-primary-dynamic">{{ formatCurrency(srv.valor_acordado) }}</span>
                            </div>

                            <button 
                                @click="abrirModalAgendamento(srv)"
                                class="inline-flex items-center gap-1.5 px-3 py-2 bg-primary-dynamic text-white hover:opacity-90 rounded-xl text-xs font-black uppercase tracking-wider shadow-sm transition-all"
                            >
                                <CalendarDaysIcon class="w-4 h-4" />
                                Agendar
                            </button>
                        </div>
                    </div>
                </div>
                <div v-else class="zynix-card p-8 text-center border-dashed">
                    <TagIcon class="w-10 h-10 text-slate-300 mx-auto mb-3" />
                    <p class="text-sm text-[var(--text-muted)] font-bold">Este parceiro ainda não cadastrou serviços/produtos.</p>
                </div>
            </div>

            <!-- Minhas Solicitações e Agendamentos neste Prestador -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <ClipboardDocumentListIcon class="w-5 h-5 text-primary-dynamic" />
                    <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">Seu Histórico de Agendamentos neste Parceiro</h3>
                </div>

                <div v-if="solicitacoes.length > 0" class="space-y-4">
                    <div v-for="sol in solicitacoes" :key="sol.id" :class="['zynix-card p-5 border-l-4 transition-all hover:shadow-md', getStatus(sol.status).border]">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div :class="['w-10 h-10 rounded-full flex items-center justify-center shrink-0', getStatus(sol.status).bg, getStatus(sol.status).color]">
                                    <component :is="getStatus(sol.status).icon" class="w-5 h-5" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-black text-[var(--text-heading)] tracking-tight">{{ sol.servico_produto }}</h4>
                                        <span v-if="sol.codigo_guia" class="text-[10px] font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 px-2 py-0.5 rounded">
                                            {{ sol.codigo_guia }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-[var(--text-muted)] font-bold mt-0.5">
                                        Beneficiário: {{ sol.beneficiario }}
                                    </p>
                                    <div class="flex flex-wrap items-center gap-3 mt-1.5">
                                        <span :class="['text-[10px] font-black uppercase tracking-widest', getStatus(sol.status).color]">
                                            {{ getStatus(sol.status).label }}
                                        </span>
                                        <span v-if="sol.data_agendada" class="text-[10px] font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1">
                                            <CalendarDaysIcon class="w-3.5 h-3.5 text-primary-dynamic" />
                                            Data: {{ sol.data_agendada }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-bold">Solicitado em {{ sol.data }}</span>
                                    </div>
                                    <div v-if="sol.observacoes" class="flex items-start gap-1.5 mt-2">
                                        <ChatBubbleBottomCenterTextIcon class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5" />
                                        <p class="text-xs text-[var(--text-muted)] font-medium italic">{{ sol.observacoes }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="sol.valor > 0" class="text-right">
                                <div class="flex items-center gap-1.5 text-sm font-black text-[var(--text-heading)]">
                                    <CurrencyDollarIcon class="w-4 h-4 text-slate-400" />
                                    {{ formatCurrency(sol.valor) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="zynix-card p-8 text-center border-dashed">
                    <ClipboardDocumentListIcon class="w-10 h-10 text-slate-300 mx-auto mb-3" />
                    <p class="text-sm text-[var(--text-muted)] font-bold">Você ainda não possui agendamentos com este parceiro.</p>
                    <p class="text-xs text-slate-400 mt-1 font-medium">
                        Escolha um serviço acima e clique no botão Agendar para emitir sua guia instantânea.
                    </p>
                </div>
            </div>

        </div>

        <!-- Modal de Solicitação e Agendamento Online -->
        <div v-if="showModalAgendar" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-[var(--border-color)] animate-in fade-in zoom-in-95">
                
                <div class="flex items-center justify-between border-b border-[var(--border-color)] pb-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary-dynamic/10 text-primary-dynamic flex items-center justify-center">
                            <CalendarDaysIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="text-base font-black text-[var(--text-heading)] tracking-tight">Agendamento Online</h3>
                            <p class="text-xs text-[var(--text-muted)]">{{ prestador.nome }}</p>
                        </div>
                    </div>
                    <button @click="showModalAgendar = false" class="text-slate-400 hover:text-slate-600">
                        <XCircleIcon class="w-6 h-6" />
                    </button>
                </div>

                <div class="space-y-4">
                    
                    <!-- Resumo do Procedimento -->
                    <div class="bg-slate-50 dark:bg-slate-800/40 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Procedimento Selecionado</span>
                            <h4 class="text-sm font-black text-[var(--text-heading)]">{{ servicoSelecionado?.nome }}</h4>
                        </div>
                        <span class="text-sm font-black text-primary-dynamic">{{ formatCurrency(servicoSelecionado?.valor_acordado) }}</span>
                    </div>

                    <!-- Seleção de Beneficiário -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-[var(--text-heading)] mb-1">
                            Para quem é o atendimento?
                        </label>
                        <select 
                            v-model="formAgendamento.dependente_id" 
                            @change="verificarElegibilidade"
                            class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-primary-dynamic focus:ring focus:ring-primary-dynamic/20 font-bold"
                        >
                            <option value="">Para mim (Titular)</option>
                            <option v-for="dep in dependentes" :key="dep.id" :value="dep.id">
                                {{ dep.nome }} ({{ dep.parentesco }})
                            </option>
                        </select>
                    </div>

                    <!-- Motor de Elegibilidade em Tempo Real -->
                    <div v-if="checandoElegibilidade" class="p-3 bg-slate-50 dark:bg-slate-800 rounded-xl flex items-center gap-2 text-xs text-slate-500">
                        <svg class="animate-spin h-4 w-4 text-primary-dynamic" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Validando requisitos e adimplência junto à cooperativa...</span>
                    </div>

                    <div v-else-if="elegibilidadeResultado">
                        <!-- Apto / Sucesso -->
                        <div v-if="elegibilidadeResultado.apto" class="p-3.5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-800 dark:text-emerald-300 text-xs font-medium flex items-start gap-2.5">
                            <CheckBadgeIcon class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" />
                            <div>
                                <p class="font-black text-emerald-700 dark:text-emerald-400">Elegibilidade Confirmada!</p>
                                <p class="text-[11px] mt-0.5">Seu cadastro está 100% regular. A autorização digital será emitida automaticamente.</p>
                            </div>
                        </div>

                        <!-- Bloqueio / Inadimplência -->
                        <div v-else class="p-3.5 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl text-rose-800 dark:text-rose-300 text-xs font-medium flex items-start gap-2.5">
                            <ExclamationTriangleIcon class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" />
                            <div>
                                <p class="font-black text-rose-700 dark:text-rose-400">Atendimento Não Liberado</p>
                                <p class="text-[11px] mt-0.5">{{ elegibilidadeResultado.motivo }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Campos de Data e Turno (Aparecem quando apto) -->
                    <div v-if="elegibilidadeResultado?.apto" class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-[var(--text-heading)] mb-1">
                                Data Preferencial
                            </label>
                            <input 
                                type="date" 
                                v-model="formAgendamento.data_agendada" 
                                :min="minDate"
                                class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-primary-dynamic font-bold"
                                required
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-[var(--text-heading)] mb-1">
                                Turno Preferencial
                            </label>
                            <select 
                                v-model="formAgendamento.turno"
                                class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-primary-dynamic font-bold"
                            >
                                <option value="manha">Manhã (a partir de 09h)</option>
                                <option value="tarde">Tarde (a partir de 14h)</option>
                                <option value="noite">Noite (a partir de 18h)</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="elegibilidadeResultado?.apto">
                        <label class="block text-xs font-bold text-[var(--text-heading)] mb-1">
                            Observações para a Clínica / Sintomas (Opcional):
                        </label>
                        <textarea 
                            v-model="formAgendamento.observacoes"
                            rows="2"
                            class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-primary-dynamic"
                            placeholder="Ex: Consulta preventiva de rotina."
                        ></textarea>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-[var(--border-color)]">
                    <button 
                        @click="showModalAgendar = false" 
                        class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                    >
                        Cancelar
                    </button>
                    <button 
                        v-if="elegibilidadeResultado?.apto"
                        @click="submeterAgendamento"
                        :disabled="formAgendamento.processing"
                        class="px-5 py-2.5 bg-primary-dynamic hover:opacity-90 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-md transition-all flex items-center gap-2"
                    >
                        <CheckBadgeIcon class="w-4 h-4" />
                        Confirmar e Emitir Guia Digital
                    </button>
                </div>

            </div>
        </div>

    </CooperadoLayout>
</template>

<style scoped>
.text-primary-dynamic { color: v-bind(primaryColor); }
.bg-primary-dynamic { background-color: v-bind(primaryColor) !important; }
.border-primary-dynamic { border-color: v-bind(primaryColor) !important; }
</style>
