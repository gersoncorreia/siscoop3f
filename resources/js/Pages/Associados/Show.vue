<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref, computed } from 'vue';
import { 
    UserIcon, 
    PencilSquareIcon, 
    ChevronLeftIcon,
    ChevronRightIcon,
    IdentificationIcon,
    CalendarDaysIcon,
    BanknotesIcon,
    UserGroupIcon,
    DocumentTextIcon,
    PhoneIcon,
    EnvelopeIcon,
    MapPinIcon,
    CheckCircleIcon,
    ClockIcon,
    XCircleIcon,
    ArrowDownTrayIcon,
    ShieldCheckIcon,
    CreditCardIcon,
    ShareIcon,
    ClipboardIcon,
    CheckIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    associado: Object,
    agendamentos: Array,
    lancamentos: Array,
    resumoFinanceiro: Object,
});

const activeTab = ref('visao_geral');
const copiedDepId = ref(null);

const isFundacao = computed(() => usePage().props.tenant?.tipo === 'fundacao');
const entityLabel = computed(() => isFundacao.value ? 'Cliente' : 'Cooperado');

const getInitials = (name) => {
    if (!name) return '??';
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
};

const copyDepMagicLink = (dep) => {
    const token = dep.access_token || 'temp_' + dep.id;
    const url = `${window.location.protocol}//${window.location.host}/acesso-dependente/${token}`;
    navigator.clipboard.writeText(url);
    copiedDepId.value = dep.id;
    setTimeout(() => copiedDepId.value = null, 2000);
};

// Paginação Reativa (8 itens por página)
const ITEMS_PER_PAGE = 8;

const agendamentosPage = ref(1);
const lancamentosPage = ref(1);
const dependentesPage = ref(1);
const documentosPage = ref(1);

const paginatedAgendamentos = computed(() => {
    if (!props.agendamentos) return [];
    const start = (agendamentosPage.value - 1) * ITEMS_PER_PAGE;
    return props.agendamentos.slice(start, start + ITEMS_PER_PAGE);
});
const totalAgendamentosPages = computed(() => Math.ceil((props.agendamentos?.length || 0) / ITEMS_PER_PAGE) || 1);

const paginatedLancamentos = computed(() => {
    if (!props.lancamentos) return [];
    const start = (lancamentosPage.value - 1) * ITEMS_PER_PAGE;
    return props.lancamentos.slice(start, start + ITEMS_PER_PAGE);
});
const totalLancamentosPages = computed(() => Math.ceil((props.lancamentos?.length || 0) / ITEMS_PER_PAGE) || 1);

const paginatedDependentes = computed(() => {
    const list = props.associado?.dependentes || [];
    const start = (dependentesPage.value - 1) * ITEMS_PER_PAGE;
    return list.slice(start, start + ITEMS_PER_PAGE);
});
const totalDependentesPages = computed(() => Math.ceil((props.associado?.dependentes?.length || 0) / ITEMS_PER_PAGE) || 1);

const paginatedDocumentos = computed(() => {
    const list = props.associado?.documentos || [];
    const start = (documentosPage.value - 1) * ITEMS_PER_PAGE;
    return list.slice(start, start + ITEMS_PER_PAGE);
});
const totalDocumentosPages = computed(() => Math.ceil((props.associado?.documentos?.length || 0) / ITEMS_PER_PAGE) || 1);
</script>

<template>
    <Head :title="`Perfil do ${entityLabel} - ${associado.nome}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <Breadcrumbs :items="[{ label: isFundacao ? 'Clientes' : 'Cooperados', href: route('associados.index') }, { label: associado.nome }]" />
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1 flex items-center gap-2">
                        <span>Perfil do {{ entityLabel }}</span>
                        <span class="text-xs px-2.5 py-1 rounded-full font-black uppercase tracking-wider border"
                            :class="{
                                'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20': associado.status === 'ativo',
                                'bg-amber-50 text-amber-600 border-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20': associado.status === 'pendente' || associado.status === 'analise_documental',
                                'bg-slate-50 text-slate-600 border-slate-100 dark:bg-slate-500/10 dark:text-slate-400 dark:border-slate-500/20': associado.status === 'inativo'
                            }"
                        >
                            {{ associado.status }}
                        </span>
                    </h2>
                    <p class="text-sm text-[var(--text-muted)] mt-0.5">Visão 360° do cadastro, atendimentos, financeiro e dependentes.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('associados.edit', associado.id)">
                        <PrimaryButton class="!rounded-full !px-6 !py-2.5 !text-[10px] !font-black !uppercase !tracking-widest shadow-lg shadow-primary-dynamic/20 flex items-center gap-2">
                            <PencilSquareIcon class="w-4 h-4" />
                            Editar Informações
                        </PrimaryButton>
                    </Link>
                    <Link :href="route('associados.index')">
                        <SecondaryButton class="!rounded-full !px-5 !py-2.5 !text-[10px] !font-black !uppercase !tracking-widest">
                            <ChevronLeftIcon class="w-4 h-4 mr-1.5" />
                            Voltar
                        </SecondaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Header Profile Card Banner -->
            <div class="zynix-card p-6 md:p-8 bg-gradient-to-r from-primary-dynamic/10 via-[var(--bg-card)] to-[var(--bg-card)] relative overflow-hidden">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative z-10">
                    <div class="flex items-center gap-5">
                        <div class="w-20 h-20 rounded-3xl bg-primary-dynamic text-white font-black text-2xl flex items-center justify-center shadow-xl shadow-primary-dynamic/30 ring-4 ring-white dark:ring-slate-800">
                            {{ getInitials(associado.nome) }}
                        </div>
                        <div>
                            <h1 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">{{ associado.nome }}</h1>
                            <div class="flex flex-wrap items-center gap-3 mt-1.5 text-xs text-slate-500 font-medium">
                                <span class="font-mono bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-lg text-slate-600 dark:text-slate-300 font-bold">CPF: {{ associado.cpf || 'Não informado' }}</span>
                                <span>•</span>
                                <span>Matrícula: <strong class="text-[var(--text-heading)]">#{{ associado.matricula || associado.id }}</strong></span>
                                <span>•</span>
                                <span>Adesão: <strong class="text-[var(--text-heading)]">{{ associado.data_adesao || '—' }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Carteirinha Quick Status Badge -->
                    <div v-if="isFundacao" class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md p-4 rounded-2xl border border-[var(--border-color)] shadow-sm flex items-center gap-4">
                        <div class="p-3 bg-amber-500/10 text-amber-500 rounded-xl">
                            <IdentificationIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Carteirinha Digital</p>
                            <p class="text-xs font-mono font-bold text-[var(--text-heading)] mt-0.5">{{ associado.carteirinha_numero || 'Não Gerada' }}</p>
                            <p class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 mt-0.5" v-if="associado.carteirinha_validade">Validade: {{ associado.carteirinha_validade }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary KPI Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="zynix-card p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Status Geral</p>
                            <h4 class="text-lg font-black text-[var(--text-heading)] capitalize mt-1">{{ associado.status }}</h4>
                        </div>
                        <div class="p-3 rounded-2xl bg-emerald-500/10 text-emerald-500">
                            <ShieldCheckIcon class="w-6 h-6" />
                        </div>
                    </div>
                </div>

                <div class="zynix-card p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Atendimentos</p>
                            <h4 class="text-lg font-black text-[var(--text-heading)] mt-1">{{ agendamentos?.length || 0 }} Realizados</h4>
                        </div>
                        <div class="p-3 rounded-2xl bg-blue-500/10 text-blue-500">
                            <CalendarDaysIcon class="w-6 h-6" />
                        </div>
                    </div>
                </div>

                <div class="zynix-card p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Situação Financeira</p>
                            <h4 class="text-lg font-black text-rose-500 mt-1" v-if="resumoFinanceiro.total_pendente > 0">
                                R$ {{ resumoFinanceiro.total_pendente.toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }} Pendente
                            </h4>
                            <h4 class="text-lg font-black text-emerald-600 mt-1" v-else>
                                Em dia (R$ {{ resumoFinanceiro.total_pago.toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }})
                            </h4>
                        </div>
                        <div class="p-3 rounded-2xl bg-rose-500/10 text-rose-500" v-if="resumoFinanceiro.total_pendente > 0">
                            <BanknotesIcon class="w-6 h-6" />
                        </div>
                        <div class="p-3 rounded-2xl bg-emerald-500/10 text-emerald-500" v-else>
                            <CheckCircleIcon class="w-6 h-6" />
                        </div>
                    </div>
                </div>

                <div class="zynix-card p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Dependentes</p>
                            <h4 class="text-lg font-black text-[var(--text-heading)] mt-1">{{ associado.dependentes?.length || 0 }} Cadastrados</h4>
                        </div>
                        <div class="p-3 rounded-2xl bg-amber-500/10 text-amber-500">
                            <UserGroupIcon class="w-6 h-6" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Buttons Bar -->
            <div class="flex items-center gap-2 border-b border-[var(--border-color)] pb-3 overflow-x-auto">
                <button 
                    @click="activeTab = 'visao_geral'"
                    :class="[
                        'px-5 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2 whitespace-nowrap',
                        activeTab === 'visao_geral' 
                            ? 'bg-primary-dynamic text-white shadow-lg shadow-primary-dynamic/20' 
                            : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'
                    ]"
                >
                    <UserIcon class="w-4 h-4" />
                    Visão Geral
                </button>

                <button 
                    @click="activeTab = 'atendimentos'"
                    :class="[
                        'px-5 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2 whitespace-nowrap',
                        activeTab === 'atendimentos' 
                            ? 'bg-primary-dynamic text-white shadow-lg shadow-primary-dynamic/20' 
                            : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'
                    ]"
                >
                    <CalendarDaysIcon class="w-4 h-4" />
                    Atendimentos & Serviços ({{ agendamentos?.length || 0 }})
                </button>

                <button 
                    @click="activeTab = 'financeiro'"
                    :class="[
                        'px-5 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2 whitespace-nowrap',
                        activeTab === 'financeiro' 
                            ? 'bg-primary-dynamic text-white shadow-lg shadow-primary-dynamic/20' 
                            : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'
                    ]"
                >
                    <BanknotesIcon class="w-4 h-4" />
                    Financeiro & Mensalidades
                    <span v-if="resumoFinanceiro.qtd_pendentes > 0" class="px-2 py-0.5 rounded-full text-[10px] bg-rose-500 text-white font-bold">{{ resumoFinanceiro.qtd_pendentes }}</span>
                </button>

                <button 
                    @click="activeTab = 'dependentes'"
                    :class="[
                        'px-5 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2 whitespace-nowrap',
                        activeTab === 'dependentes' 
                            ? 'bg-primary-dynamic text-white shadow-lg shadow-primary-dynamic/20' 
                            : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'
                    ]"
                >
                    <UserGroupIcon class="w-4 h-4" />
                    Dependentes ({{ associado.dependentes?.length || 0 }})
                </button>

                <button 
                    @click="activeTab = 'documentos'"
                    :class="[
                        'px-5 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2 whitespace-nowrap',
                        activeTab === 'documentos' 
                            ? 'bg-primary-dynamic text-white shadow-lg shadow-primary-dynamic/20' 
                            : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'
                    ]"
                >
                    <DocumentTextIcon class="w-4 h-4" />
                    Documentos ({{ associado.documentos?.length || 0 }})
                </button>
            </div>

            <!-- TAB CONTENT 1: Visão Geral -->
            <div v-if="activeTab === 'visao_geral'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Informações Cadastrais (2 Colunas) -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="zynix-card p-6">
                        <h3 class="text-base font-black text-[var(--text-heading)] mb-6 flex items-center gap-2 border-b border-[var(--border-color)] pb-3">
                            <UserIcon class="w-5 h-5 text-primary-dynamic" />
                            Dados de Identificação do {{ entityLabel }}
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Nome Completo</p>
                                <p class="font-bold text-[var(--text-heading)] mt-1">{{ associado.nome }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">CPF</p>
                                <p class="font-mono font-bold text-[var(--text-heading)] mt-1">{{ associado.cpf || '—' }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Data de Nascimento</p>
                                <p class="font-bold text-[var(--text-heading)] mt-1">{{ associado.nascimento || '—' }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Data de Adesão</p>
                                <p class="font-bold text-[var(--text-heading)] mt-1">{{ associado.data_adesao || '—' }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Forma de Pagamento da Adesão</p>
                                <p class="font-bold text-[var(--text-heading)] uppercase mt-1">{{ associado.forma_pagamento_adesao || 'Presencial / Pix' }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Desconto em Folha</p>
                                <p class="font-bold text-[var(--text-heading)] mt-1">{{ associado.autoriza_desconto_folha ? 'Sim (Autorizado)' : 'Não Autorizado' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Telefones e Contatos -->
                    <div class="zynix-card p-6">
                        <h3 class="text-base font-black text-[var(--text-heading)] mb-6 flex items-center gap-2 border-b border-[var(--border-color)] pb-3">
                            <PhoneIcon class="w-5 h-5 text-primary-dynamic" />
                            Canais de Contato & Endereços
                        </h3>

                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500">
                                    <EnvelopeIcon class="w-5 h-5" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">E-mail Principal</p>
                                    <p class="font-bold text-[var(--text-heading)] text-sm mt-0.5">{{ associado.email || '—' }}</p>
                                </div>
                            </div>

                            <div v-for="tel in associado.telefones" :key="tel.id" class="flex items-center gap-3">
                                <div class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500">
                                    <PhoneIcon class="w-5 h-5" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Telefone ({{ tel.tipo || 'Celular' }})</p>
                                    <p class="font-mono font-bold text-[var(--text-heading)] text-sm mt-0.5">{{ tel.numero }}</p>
                                </div>
                            </div>

                            <div v-for="end in associado.enderecos" :key="end.id" class="flex items-center gap-3">
                                <div class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500">
                                    <MapPinIcon class="w-5 h-5" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Endereço Registrado</p>
                                    <p class="font-bold text-[var(--text-heading)] text-sm mt-0.5">
                                        {{ end.logradouro }}, {{ end.numero }} {{ end.complemento }} — {{ end.bairro }}, {{ end.cidade }}/{{ end.uf }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Painel da Entidade (1 Coluna) -->
                <div class="space-y-6">
                    <div class="zynix-card p-6 bg-gradient-to-br from-primary-dynamic/5 to-transparent">
                        <h3 class="text-base font-black text-[var(--text-heading)] mb-4 flex items-center gap-2">
                            <IdentificationIcon class="w-5 h-5 text-primary-dynamic" />
                            {{ isFundacao ? 'Plano da Fundação Santa Casa' : 'Resumo da Cota Capital' }}
                        </h3>

                        <div class="space-y-4 text-xs">
                            <div class="p-4 bg-white dark:bg-slate-800 rounded-2xl border border-[var(--border-color)]">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Carteirinha Digital</p>
                                <p class="text-base font-mono font-black text-primary-dynamic mt-1">{{ associado.carteirinha_numero || 'Não emitida' }}</p>
                                <p class="text-[10px] font-bold text-slate-500 mt-1">Validade: {{ associado.carteirinha_validade || 'Indefinido' }}</p>
                            </div>

                            <div class="p-4 bg-white dark:bg-slate-800 rounded-2xl border border-[var(--border-color)]" v-if="associado.aprovado_em">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Auditoria de Aprovação</p>
                                <p class="font-bold text-[var(--text-heading)] mt-1">Aprovado por: {{ associado.aprovado_por_nome }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">Em: {{ associado.aprovado_em }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB CONTENT 2: Atendimentos & Serviços -->
            <div v-if="activeTab === 'atendimentos'" class="zynix-card !p-0 overflow-hidden">
                <div v-if="!agendamentos || agendamentos.length === 0" class="p-12 text-center">
                    <CalendarDaysIcon class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Nenhum atendimento ou consulta registrado para este cliente.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="zynix-table w-full">
                        <thead>
                            <tr>
                                <th class="pl-8">Data / Hora</th>
                                <th>Serviço / Especialidade</th>
                                <th>Prestador de Saúde</th>
                                <th class="text-center">Valor (R$)</th>
                                <th class="text-center">Status</th>
                                <th class="text-right pr-8">Comprovante / Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="ag in paginatedAgendamentos" :key="ag.id">
                                <td class="pl-8 font-mono text-xs font-bold text-[var(--text-heading)]">
                                    {{ ag.data_hora }}
                                </td>
                                <td>
                                    <p class="font-bold text-[var(--text-heading)] text-xs">{{ ag.servico_nome || ag.especialidade }}</p>
                                    <p class="text-[10px] text-slate-400 uppercase tracking-wider">{{ ag.especialidade }}</p>
                                </td>
                                <td class="text-xs font-medium">
                                    {{ ag.prestador_nome }}
                                </td>
                                <td class="text-center font-mono font-bold text-xs">
                                    R$ {{ ag.valor.toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                                </td>
                                <td class="text-center">
                                    <span 
                                        :class="{
                                            'bg-emerald-50 text-emerald-600 border-emerald-100': ag.status === 'realizado' || ag.status === 'confirmado',
                                            'bg-amber-50 text-amber-600 border-amber-100': ag.status === 'pendente',
                                            'bg-rose-50 text-rose-600 border-rose-100': ag.status === 'cancelado'
                                        }"
                                        class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider border rounded-full inline-flex items-center"
                                    >
                                        {{ ag.status }}
                                    </span>
                                </td>
                                <td class="text-right pr-8">
                                    <a v-if="ag.comprovante_pix_url" :href="ag.comprovante_pix_url" target="_blank" class="text-xs text-primary-dynamic font-bold underline inline-flex items-center gap-1">
                                        Ver Comprovante PIX
                                    </a>
                                    <span v-else class="text-[10px] text-slate-400">—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação Atendimentos -->
                <div class="p-4 border-t border-[var(--border-color)] flex items-center justify-between" v-if="totalAgendamentosPages > 1">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        Página {{ agendamentosPage }} de {{ totalAgendamentosPages }} ({{ agendamentos.length }} atendimentos)
                    </div>
                    <div class="flex items-center gap-2">
                        <button 
                            @click="agendamentosPage--" 
                            :disabled="agendamentosPage === 1"
                            class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 dark:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center"
                        >
                            <ChevronLeftIcon class="w-3.5 h-3.5 mr-1" /> Anterior
                        </button>
                        <button 
                            @click="agendamentosPage++" 
                            :disabled="agendamentosPage === totalAgendamentosPages"
                            class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 dark:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center"
                        >
                            Próximo <ChevronRightIcon class="w-3.5 h-3.5 ml-1" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- TAB CONTENT 3: Financeiro & Pagamentos -->
            <div v-if="activeTab === 'financeiro'" class="space-y-6">
                <!-- Summary Card -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="zynix-card p-5 bg-emerald-50/50 dark:bg-emerald-500/5 border-emerald-100 dark:border-emerald-500/20">
                        <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Total Quitado</p>
                        <h3 class="text-2xl font-black text-emerald-700 dark:text-emerald-400 mt-1">R$ {{ resumoFinanceiro.total_pago.toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}</h3>
                        <p class="text-[10px] text-slate-400 mt-0.5">{{ resumoFinanceiro.qtd_pagos }} lançamentos pagos</p>
                    </div>

                    <div class="zynix-card p-5 bg-rose-50/50 dark:bg-rose-500/5 border-rose-100 dark:border-rose-500/20">
                        <p class="text-[10px] font-black uppercase tracking-widest text-rose-600 dark:text-rose-400">Pendências em Aberto</p>
                        <h3 class="text-2xl font-black text-rose-700 dark:text-rose-400 mt-1">R$ {{ resumoFinanceiro.total_pendente.toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}</h3>
                        <p class="text-[10px] text-slate-400 mt-0.5">{{ resumoFinanceiro.qtd_pendentes }} faturas a pagar</p>
                    </div>

                    <div class="zynix-card p-5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total de Lançamentos</p>
                        <h3 class="text-2xl font-black text-[var(--text-heading)] mt-1">{{ lancamentos?.length || 0 }} Registros</h3>
                        <p class="text-[10px] text-slate-400 mt-0.5">Histórico financeiro completo</p>
                    </div>
                </div>

                <div class="zynix-card !p-0 overflow-hidden">
                    <div v-if="!lancamentos || lancamentos.length === 0" class="p-12 text-center">
                        <BanknotesIcon class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Nenhum lançamento financeiro registrado.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="zynix-table w-full">
                            <thead>
                                <tr>
                                    <th class="pl-8">Descrição / Categoria</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-center">Vencimento</th>
                                    <th class="text-center">Valor (R$)</th>
                                    <th class="text-right pr-8">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="l in paginatedLancamentos" :key="l.id">
                                    <td class="pl-8">
                                        <p class="font-bold text-[var(--text-heading)] text-xs">{{ l.descricao }}</p>
                                        <p class="text-[10px] text-slate-400 uppercase tracking-wider">{{ l.categoria || 'Geral' }}</p>
                                    </td>
                                    <td class="text-center">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase" :class="l.tipo === 'entrada' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">
                                            {{ l.tipo }}
                                        </span>
                                    </td>
                                    <td class="text-center font-mono text-xs font-medium">
                                        {{ l.data_vencimento }}
                                    </td>
                                    <td class="text-center font-mono font-bold text-xs">
                                        R$ {{ l.valor.toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                                    </td>
                                    <td class="text-right pr-8">
                                        <span 
                                            :class="l.status === 'pago' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100'"
                                            class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider border rounded-full inline-flex items-center"
                                        >
                                            {{ l.status === 'pago' ? '✓ Pago' : '⏳ Pendente' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação Financeiro -->
                    <div class="p-4 border-t border-[var(--border-color)] flex items-center justify-between" v-if="totalLancamentosPages > 1">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            Página {{ lancamentosPage }} de {{ totalLancamentosPages }} ({{ lancamentos.length }} lançamentos)
                        </div>
                        <div class="flex items-center gap-2">
                            <button 
                                @click="lancamentosPage--" 
                                :disabled="lancamentosPage === 1"
                                class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 dark:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center"
                            >
                                <ChevronLeftIcon class="w-3.5 h-3.5 mr-1" /> Anterior
                            </button>
                            <button 
                                @click="lancamentosPage++" 
                                :disabled="lancamentosPage === totalLancamentosPages"
                                class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 dark:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center"
                            >
                                Próximo <ChevronRightIcon class="w-3.5 h-3.5 ml-1" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB CONTENT 4: Dependentes / Familiares -->
            <div v-if="activeTab === 'dependentes'" class="zynix-card !p-0 overflow-hidden">
                <div v-if="!associado.dependentes || associado.dependentes.length === 0" class="p-12 text-center">
                    <UserGroupIcon class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Nenhum dependente cadastrado para este cliente.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="zynix-table w-full">
                        <thead>
                            <tr>
                                <th class="pl-8">Nome do Dependente</th>
                                <th class="text-center">Parentesco</th>
                                <th class="text-center">CPF</th>
                                <th class="text-center">Tipo Dependência</th>
                                <th class="text-right pr-8">Acesso & Link Mágico</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="dep in paginatedDependentes" :key="dep.id">
                                <td class="pl-8">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 font-black text-xs flex items-center justify-center">
                                            {{ getInitials(dep.nome) }}
                                        </div>
                                        <div class="font-bold text-[var(--text-heading)] text-xs">{{ dep.nome }}</div>
                                    </div>
                                </td>
                                <td class="text-center text-xs font-medium capitalize">
                                    {{ dep.parentesco || 'Dependente' }}
                                </td>
                                <td class="text-center font-mono text-xs text-slate-600">
                                    {{ dep.cpf || '—' }}
                                </td>
                                <td class="text-center">
                                    <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-full" :class="dep.tipo_dependencia === 'nao_legal' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700'">
                                        {{ dep.tipo_dependencia === 'nao_legal' ? 'Não Legal (R$ 100)' : 'Legal (R$ 80)' }}
                                    </span>
                                </td>
                                <td class="text-right pr-8">
                                    <button 
                                        @click="copyDepMagicLink(dep)"
                                        :class="copiedDepId === dep.id ? 'bg-emerald-500 text-white' : 'bg-primary-dynamic/10 text-primary-dynamic hover:bg-primary-dynamic hover:text-white'"
                                        class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider inline-flex items-center gap-1.5 transition-all shadow-sm"
                                        title="Copiar Link Mágico de Acesso do Dependente (Validação 5 dígitos CPF)"
                                    >
                                        <component :is="copiedDepId === dep.id ? CheckIcon : ShareIcon" class="w-3.5 h-3.5" />
                                        {{ copiedDepId === dep.id ? 'Copiado!' : 'Link Mágico' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação Dependentes -->
                <div class="p-4 border-t border-[var(--border-color)] flex items-center justify-between" v-if="totalDependentesPages > 1">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        Página {{ dependentesPage }} de {{ totalDependentesPages }} ({{ associado.dependentes.length }} dependentes)
                    </div>
                    <div class="flex items-center gap-2">
                        <button 
                            @click="dependentesPage--" 
                            :disabled="dependentesPage === 1"
                            class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 dark:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center"
                        >
                            <ChevronLeftIcon class="w-3.5 h-3.5 mr-1" /> Anterior
                        </button>
                        <button 
                            @click="dependentesPage++" 
                            :disabled="dependentesPage === totalDependentesPages"
                            class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 dark:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center"
                        >
                            Próximo <ChevronRightIcon class="w-3.5 h-3.5 ml-1" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- TAB CONTENT 5: Documentos Digitais -->
            <div v-if="activeTab === 'documentos'" class="zynix-card !p-0 overflow-hidden">
                <div v-if="!associado.documentos || associado.documentos.length === 0" class="p-12 text-center">
                    <DocumentTextIcon class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Nenhum documento anexado ainda.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="zynix-table w-full">
                        <thead>
                            <tr>
                                <th class="pl-8">Título do Arquivo</th>
                                <th class="text-center">Tipo</th>
                                <th class="text-center">Status Auditoria</th>
                                <th class="text-right pr-8">Download</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="doc in paginatedDocumentos" :key="doc.id">
                                <td class="pl-8">
                                    <p class="font-bold text-[var(--text-heading)] text-xs">{{ doc.titulo }}</p>
                                    <p v-if="doc.motivo_rejeicao" class="text-[10px] text-rose-500 mt-0.5">Motivo: {{ doc.motivo_rejeicao }}</p>
                                </td>
                                <td class="text-center text-xs font-medium">
                                    {{ doc.tipo || 'Outros' }}
                                </td>
                                <td class="text-center">
                                    <span 
                                        :class="{
                                            'bg-emerald-50 text-emerald-600 border-emerald-100': doc.status === 'aprovado',
                                            'bg-amber-50 text-amber-600 border-amber-100': doc.status === 'pendente',
                                            'bg-rose-50 text-rose-600 border-rose-100': doc.status === 'rejeitado'
                                        }"
                                        class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider border rounded-full inline-flex items-center"
                                    >
                                        {{ doc.status }}
                                    </span>
                                </td>
                                <td class="text-right pr-8">
                                    <a :href="doc.download_url" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-primary-dynamic hover:text-white rounded-xl text-[11px] font-black uppercase tracking-wider inline-flex items-center gap-1.5 transition-all">
                                        <ArrowDownTrayIcon class="w-3.5 h-3.5" />
                                        Baixar
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação Documentos -->
                <div class="p-4 border-t border-[var(--border-color)] flex items-center justify-between" v-if="totalDocumentosPages > 1">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        Página {{ documentosPage }} de {{ totalDocumentosPages }} ({{ associado.documentos.length }} documentos)
                    </div>
                    <div class="flex items-center gap-2">
                        <button 
                            @click="documentosPage--" 
                            :disabled="documentosPage === 1"
                            class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 dark:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center"
                        >
                            <ChevronLeftIcon class="w-3.5 h-3.5 mr-1" /> Anterior
                        </button>
                        <button 
                            @click="documentosPage++" 
                            :disabled="documentosPage === totalDocumentosPages"
                            class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 dark:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center"
                        >
                            Próximo <ChevronRightIcon class="w-3.5 h-3.5 ml-1" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
</style>
