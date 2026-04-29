<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
} from 'chart.js';
import {
    UsersIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    BanknotesIcon,
    BuildingLibraryIcon,
    ShieldCheckIcon,
    BuildingOffice2Icon,
    DocumentTextIcon,
    ClockIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
    HeartIcon,
    CurrencyDollarIcon,
    TrashIcon,
    PencilIcon,
    XMarkIcon,
    CheckIcon
} from '@heroicons/vue/24/outline';
import { PlusIcon } from '@heroicons/vue/24/solid';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { useForm } from '@inertiajs/vue3';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const page = usePage();
const isCentral = computed(() => page.props.isCentral);
const isTenant = computed(() => page.props.isTenant);
const tenant = computed(() => page.props.tenant || null);
const stats = computed(() => page.props.stats || {});
const superAdminKpis = computed(() => page.props.superAdminKpis || null);
const chartDataRaw = computed(() => page.props.chartData || []);
const ultimosLancamentos = computed(() => page.props.ultimosLancamentos || []);
const primaryColor = computed(() => page.props.tenant?.primary_color || '#2563eb');

const greeting = computed(() => {
    const h = new Date().getHours();
    if (h < 12) return 'Bom dia';
    if (h < 18) return 'Boa tarde';
    return 'Boa noite';
});

const saldoMes = computed(() => stats.value.receitasMes - stats.value.despesasMes);

const chartData = computed(() => ({
    labels: chartDataRaw.value.map(d => d.label),
    datasets: [
        {
            label: 'Receitas',
            data: chartDataRaw.value.map(d => d.receitas),
            backgroundColor: primaryColor.value + 'cc',
            hoverBackgroundColor: primaryColor.value,
            borderRadius: 6,
        },
        {
            label: 'Despesas',
            data: chartDataRaw.value.map(d => d.despesas),
            backgroundColor: '#ef4444cc',
            hoverBackgroundColor: '#ef4444',
            borderRadius: 6,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
            align: 'end',
            labels: {
                usePointStyle: true,
                pointStyle: 'circle',
                padding: 20,
                font: { size: 11, weight: '700' },
                color: '#64748b'
            },
        },
        tooltip: {
            backgroundColor: '#1b1c22',
            padding: 12,
            cornerRadius: 8,
            titleFont: { size: 12, weight: 'bold' }
        }
    },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 10, weight: '600' }, color: '#94a3b8' } },
        y: { 
            grid: { color: 'rgba(148, 163, 184, 0.1)', drawBorder: false }, 
            ticks: { font: { size: 10 }, color: '#94a3b8', callback: (v) => 'R$ ' + v.toLocaleString('pt-BR') } 
        }
    },
};

function formatCurrency(v) {
    return (v || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

// ------ SUPER ADMIN LOGIC ------
const tenants = computed(() => page.props.tenants || []);
const isTenantModalOpen = ref(false);
const isDeleteTenantModalOpen = ref(false);
const isRestoreTenantModalOpen = ref(false);
const isEditingTenant = ref(false);
const currentTenantId = ref(null);
const currentTenantActionType = ref(null);

const tenantForm = useForm({
    nome: '',
    ramo: '',
    admin_nome: '',
    admin_email: '',
});

function openTenantModal(tenantEdit = null) {
    if (tenantEdit) {
        isEditingTenant.value = true;
        currentTenantId.value = tenantEdit.id;
        tenantForm.nome = tenantEdit.nome;
        tenantForm.ramo = tenantEdit.ramo;
    } else {
        isEditingTenant.value = false;
        currentTenantId.value = null;
        tenantForm.reset();
    }
    isTenantModalOpen.value = true;
}

function closeTenantModal() {
    isTenantModalOpen.value = false;
    tenantForm.reset();
}

function submitTenant() {
    if (isEditingTenant.value) {
        tenantForm.put(route('tenants.update', currentTenantId.value), { onSuccess: () => closeTenantModal() });
    } else {
        tenantForm.post(route('tenants.store'), { onSuccess: () => closeTenantModal() });
    }
}

function confirmDeleteTenant(id, actionType) {
    // actionType = 'suspend' (soft delete), 'force' (hard delete)
    currentTenantId.value = id;
    currentTenantActionType.value = actionType;
    isDeleteTenantModalOpen.value = true;
}

function deleteTenant() {
    if (currentTenantActionType.value === 'force') {
        tenantForm.delete(route('tenants.forceDelete', currentTenantId.value), { onSuccess: () => isDeleteTenantModalOpen.value = false });
    } else {
        tenantForm.delete(route('tenants.destroy', currentTenantId.value), { onSuccess: () => isDeleteTenantModalOpen.value = false });
    }
}

function confirmRestoreTenant(id) {
    currentTenantId.value = id;
    isRestoreTenantModalOpen.value = true;
}

function restoreTenant() {
    tenantForm.post(route('tenants.restore', currentTenantId.value), { onSuccess: () => isRestoreTenantModalOpen.value = false });
}

function exportTenant(id) {
    window.location.href = route('tenants.export', id);
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <Breadcrumbs :items="[]" />
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">
                        {{ greeting }}, {{ $page.props.auth.user.name.split(' ')[0] }}!
                    </h2>
                    <p class="text-sm text-[var(--text-muted)] mt-1 font-medium">
                        Resumo das atividades do portal <span class="text-primary-dynamic font-bold">{{ tenant?.nome || 'Central' }}</span>.
                    </p>
                </div>
                <div v-if="isTenant" class="flex items-center gap-3">
                    <Link href="/associados/adesoes" class="btn-primary flex items-center gap-2">
                        <PlusIcon class="w-4 h-4" />
                        Nova Adesão
                    </Link>
                </div>
                <div v-if="isCentral" class="flex items-center gap-3">
                    <button @click="openTenantModal()" class="btn-primary flex items-center gap-2">
                        <PlusIcon class="w-4 h-4" />
                        Nova Cooperativa
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-8 pb-12">
            <!-- Flash Messages -->
            <div v-if="$page.props.flash?.success" class="zynix-card !border-emerald-200 dark:!border-emerald-500/30 !bg-emerald-50 dark:!bg-emerald-500/10 relative">
                <button @click="$page.props.flash.success = null" class="absolute top-3 right-3 p-1 rounded-full hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-colors">
                    <XMarkIcon class="w-4 h-4 text-emerald-600" />
                </button>
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-emerald-100 dark:bg-emerald-500/20 rounded-xl shrink-0">
                        <CheckCircleIcon class="w-5 h-5 text-emerald-600" />
                    </div>
                    <pre class="text-sm font-bold text-emerald-700 dark:text-emerald-300 whitespace-pre-wrap leading-relaxed">{{ $page.props.flash.success }}</pre>
                </div>
            </div>
            <!-- KPIs Grid - Only for Tenant -->
            <div v-if="isTenant" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Associados KPI -->
                <div class="zynix-card group overflow-hidden relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="icon-circle bg-primary-light">
                            <UsersIcon class="w-5 h-5" />
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Ativos</span>
                    </div>
                    <div>
                        <h3 class="text-3xl font-black text-[var(--text-heading)] tracking-tighter">{{ stats.associadosAtivos || 0 }}</h3>
                        <p class="text-[11px] font-bold text-slate-400 mt-1 uppercase tracking-wider">Total de {{ stats.totalAssociados || 0 }} membros</p>
                    </div>
                    <div class="absolute -bottom-4 -right-4 w-20 h-20 bg-primary-dynamic/5 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
                </div>

                <!-- Receitas KPI -->
                <div class="zynix-card group overflow-hidden relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="icon-circle bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <ArrowTrendingUpIcon class="w-5 h-5" />
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-500">Mês Atual</span>
                    </div>
                    <div>
                        <h3 class="text-2xl lg:text-3xl font-black text-emerald-600 dark:text-emerald-400 tracking-tighter">{{ formatCurrency(stats.receitasMes) }}</h3>
                        <p class="text-[11px] font-bold text-slate-400 mt-1 uppercase tracking-wider">Entradas confirmadas</p>
                    </div>
                </div>

                <!-- Despesas KPI -->
                <div class="zynix-card group overflow-hidden relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="icon-circle bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400">
                            <ArrowTrendingDownIcon class="w-5 h-5" />
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-rose-500">Mês Atual</span>
                    </div>
                    <div>
                        <h3 class="text-2xl lg:text-3xl font-black text-rose-600 dark:text-rose-400 tracking-tighter">{{ formatCurrency(stats.despesasMes) }}</h3>
                        <p class="text-[11px] font-bold text-slate-400 mt-1 uppercase tracking-wider">Saídas programadas</p>
                    </div>
                </div>

                <!-- Saldo KPI -->
                <div class="zynix-card group overflow-hidden relative bg-primary-dynamic !text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="icon-circle bg-white/20 text-white">
                            <BuildingLibraryIcon class="w-5 h-5" />
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-white/70">Consolidado</span>
                    </div>
                    <div>
                        <h3 class="text-2xl lg:text-3xl font-black tracking-tighter">{{ formatCurrency(stats.saldoBancario) }}</h3>
                        <p class="text-[11px] font-bold text-white/70 mt-1 uppercase tracking-wider">Disponível em conta</p>
                    </div>
                </div>
            </div>

            <!-- Master Admin KPIs Grid -->
            <div v-if="isCentral" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="zynix-card">
                    <h3 class="text-xs uppercase font-black tracking-widest text-[#94a3b8]">Cooperativas Ativas</h3>
                    <p class="text-3xl font-black mt-2">{{ superAdminKpis?.totalCooperativas || 0 }}</p>
                </div>
                <div class="zynix-card">
                    <h3 class="text-xs uppercase font-black tracking-widest text-emerald-500">Novas este Mês</h3>
                    <p class="text-3xl font-black mt-2 text-emerald-600">{{ superAdminKpis?.novasEsteMes || 0 }}</p>
                </div>
                <div class="zynix-card bg-primary-dynamic !text-white !border-none">
                    <h3 class="text-xs uppercase font-black tracking-widest text-white/70">Domínios Registrados</h3>
                    <p class="text-3xl font-black mt-2 text-white">{{ superAdminKpis?.totalDominios || 0 }}</p>
                </div>
            </div>

            <!-- Main Dashboard Content (Only for Tenant) -->
            <div v-if="isTenant" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Chart Area -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="zynix-card">
                        <div class="zynix-card-header">
                            <div>
                                <h4 class="text-sm font-black text-[var(--text-heading)] uppercase tracking-widest">Fluxo de Caixa</h4>
                                <p class="text-[10px] font-bold text-slate-400 mt-1">Comparativo semestral de receitas e despesas</p>
                            </div>
                            <div class="flex gap-2">
                                <button class="p-2 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-500 hover:text-primary-dynamic transition-colors">
                                    <DocumentTextIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                        <div class="h-80 w-full">
                            <Bar v-if="chartDataRaw.length" :data="chartData" :options="chartOptions" />
                            <div v-else class="flex flex-col items-center justify-center h-full text-slate-300">
                                <CurrencyDollarIcon class="w-12 h-12 mb-2 opacity-20" />
                                <p class="text-xs font-bold uppercase tracking-widest">Dados insuficientes</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions Table -->
                    <div v-if="isTenant" class="zynix-card !p-0 overflow-hidden">
                        <div class="p-5 zynix-card-header !mb-0 border-none">
                            <h4 class="text-sm font-black text-[var(--text-heading)] uppercase tracking-widest">Últimos Lançamentos</h4>
                            <Link href="/financeiro/lancamentos" class="text-[10px] font-black text-primary-dynamic uppercase tracking-widest hover:underline">Ver Todos</Link>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="zynix-table">
                                <thead>
                                    <tr>
                                        <th>Associado</th>
                                        <th>Tipo / Valor</th>
                                        <th>Status</th>
                                        <th>Vencimento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="lanc in ultimosLancamentos" :key="lanc.id">
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-[10px] font-black text-slate-500">
                                                    {{ lanc.associado_nome.charAt(0) }}
                                                </div>
                                                <span class="font-bold text-xs">{{ lanc.associado_nome }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex flex-col">
                                                <span class="font-black text-sm" :class="lanc.tipo === 'entrada' ? 'text-emerald-500' : 'text-rose-500'">
                                                    {{ formatCurrency(lanc.valor) }}
                                                </span>
                                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ lanc.tipo }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span 
                                                class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest"
                                                :class="{
                                                    'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10': lanc.status === 'pago',
                                                    'bg-amber-50 text-amber-600 dark:bg-amber-500/10': lanc.status === 'pendente',
                                                    'bg-rose-50 text-rose-600 dark:bg-rose-500/10': lanc.status === 'atrasado',
                                                }"
                                            >
                                                {{ lanc.status }}
                                            </span>
                                        </td>
                                        <td class="text-xs font-bold text-slate-500">{{ lanc.data_vencimento }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-if="ultimosLancamentos.length === 0" class="p-12 text-center text-slate-400">
                                <p class="text-xs font-bold uppercase tracking-widest">Nenhuma transação registrada</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar Stats -->
                <div class="space-y-6">
                    <!-- Capital Social -->
                    <div class="zynix-card bg-primary-gradient !shadow-xl !shadow-primary-dynamic/20">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 rounded-xl bg-white/20">
                                <BanknotesIcon class="w-5 h-5 text-white" />
                            </div>
                            <h4 class="font-black text-xs uppercase tracking-widest text-white/90">Capital Social</h4>
                        </div>
                        <p class="text-2xl font-black text-white tracking-tighter">{{ formatCurrency(stats.capitalSocial) }}</p>
                        <div class="mt-4 pt-4 border-t border-white/10 flex justify-between items-center text-[10px] font-bold text-white/70 uppercase">
                            <span>Integralizado</span>
                            <span class="px-2 py-0.5 bg-white/20 rounded text-white">{{ stats.totalPlanos }} Planos Ativos</span>
                        </div>
                    </div>

                    <!-- Health Module Stats -->
                    <div class="zynix-card">
                        <div class="zynix-card-header">
                            <h4 class="text-xs font-black text-[var(--text-heading)] uppercase tracking-widest">Módulo de Saúde</h4>
                            <HeartIcon class="w-4 h-4 text-rose-500" />
                        </div>
                        <div class="space-y-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600">
                                        <BuildingOffice2Icon class="w-4 h-4" />
                                    </div>
                                    <span class="text-xs font-bold text-slate-500">Rede Médica</span>
                                </div>
                                <span class="text-xs font-black text-[var(--text-heading)]">{{ stats.totalPrestadores }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="p-1.5 rounded-lg bg-primary-light">
                                        <ShieldCheckIcon class="w-4 h-4" />
                                    </div>
                                    <span class="text-xs font-bold text-slate-500">Plano Ativos</span>
                                </div>
                                <span class="text-xs font-black text-[var(--text-heading)]">{{ stats.totalPlanos }}</span>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-[var(--border-color)]">
                                <div class="flex items-center gap-2">
                                    <div class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600">
                                        <DocumentTextIcon class="w-4 h-4" />
                                    </div>
                                    <span class="text-xs font-bold text-slate-500">Reembolsos</span>
                                </div>
                                <span class="text-xs font-black text-amber-500">{{ stats.reembolsosPendentes }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Support/Quick Action -->
                    <div class="zynix-card bg-slate-900 border-none relative overflow-hidden">
                        <div class="relative z-10">
                            <h4 class="text-white font-black text-sm mb-2">Precisa de Ajuda?</h4>
                            <p class="text-slate-400 text-[10px] leading-relaxed mb-4 font-medium uppercase tracking-wider">Acesse a central de suporte técnica para sanar dúvidas do portal.</p>
                            <button class="w-full py-2 bg-white text-slate-900 text-[10px] font-black uppercase tracking-widest rounded-full hover:bg-slate-200 transition-colors">
                                Abrir chamado
                            </button>
                        </div>
                        <div class="absolute -top-10 -right-10 w-32 h-32 bg-primary-dynamic/20 rounded-full blur-2xl"></div>
                    </div>
                </div>
            </div>

            <!-- Master Admin Tenants List -->
            <div v-if="isCentral" class="zynix-card overflow-hidden !p-0">
                <div class="p-6 border-b border-[var(--border-color)]">
                    <h3 class="text-lg font-black text-[var(--text-heading)]">Gestão de Inquilinos</h3>
                    <p class="text-[12px] font-medium text-slate-500">Módulo de controle de infraestrutura Master.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full zynix-table text-left">
                        <thead>
                            <tr>
                                <th class="p-4 uppercase text-[10px] font-black text-slate-400">Cooperativa</th>
                                <th class="p-4 uppercase text-[10px] font-black text-slate-400">Segmento</th>
                                <th class="p-4 uppercase text-[10px] font-black text-slate-400">Status</th>
                                <th class="p-4 uppercase text-[10px] font-black text-slate-400">Domínio Atrelado</th>
                                <th class="p-4 uppercase text-[10px] font-black text-slate-400 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="t in tenants" :key="t.id" class="border-b border-[var(--border-color)] group hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-xs font-black text-slate-600 dark:text-slate-300">
                                            {{ t.nome.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-black text-sm text-[var(--text-heading)]">{{ t.nome }}</p>
                                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">ID: {{ t.id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-md text-[10px] font-bold uppercase tracking-widest">
                                        {{ t.ramo || 'Geral' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span v-if="!t.deleted_at" class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 text-[10px] font-bold uppercase tracking-widest rounded-md">Ativo</span>
                                    <span v-else class="px-2.5 py-1 bg-amber-50 dark:bg-amber-500/10 text-amber-600 text-[10px] font-bold uppercase tracking-widest rounded-md">Suspenso</span>
                                </td>
                                <td class="p-4 font-bold text-xs text-primary-dynamic">
                                    {{ t.domains?.[0]?.domain }}
                                </td>
                                <td class="p-4 flex gap-2 justify-end">
                                    <!-- Ações Ativas -->
                                    <template v-if="!t.deleted_at">
                                        <a :href="'http://' + (t.domains?.[0]?.domain || '')" target="_blank" class="p-2 rounded-xl text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition-all font-bold" title="Acessar Painel">
                                            <ArrowTrendingUpIcon class="w-4 h-4" />
                                        </a>
                                        <button @click="openTenantModal(t)" class="p-2 rounded-xl text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-all font-bold" title="Editar Setup">
                                            <PencilIcon class="w-4 h-4" />
                                        </button>
                                        <button @click="confirmDeleteTenant(t.id, 'suspend')" class="p-2 rounded-xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-all font-bold" title="Suspender Serviço">
                                            <TrashIcon class="w-4 h-4" />
                                        </button>
                                    </template>
                                    
                                    <!-- Ações para Inativos (LGPD) -->
                                    <template v-else>
                                        <button @click="exportTenant(t.id)" class="px-3 py-1 bg-purple-50 text-purple-600 dark:bg-purple-500/10 rounded-lg text-xs font-bold transition flex items-center gap-2" title="Solicitar Base de Dados">
                                            <DocumentTextIcon class="w-3 h-3"/> Download Backup
                                        </button>
                                        <button @click="confirmRestoreTenant(t.id)" class="px-3 py-1 bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 rounded-lg text-xs font-bold transition flex items-center gap-2" title="Reativar e Restaurar Serviço">
                                            <CheckCircleIcon class="w-3 h-3"/> Reativar
                                        </button>
                                        <button @click="confirmDeleteTenant(t.id, 'force')" class="px-3 py-1 bg-rose-50 border border-rose-200 text-rose-600 dark:bg-rose-500/10 dark:border-rose-500/30 rounded-lg text-xs font-black uppercase tracking-widest hover:bg-rose-600 hover:text-white transition flex items-center gap-2" title="Destruição LGPD">
                                            <ExclamationCircleIcon class="w-3 h-3"/> Dropar Base
                                        </button>
                                    </template>
                                </td>
                            </tr>
                            <tr v-if="tenants.length === 0">
                                <td colspan="5" class="p-8 text-center text-[11px] font-bold uppercase tracking-widest text-slate-400">
                                    Nenhuma cooperativa foi registrada ainda.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Gerenciar Cooperativa -->
            <Modal :show="isTenantModalOpen" @close="closeTenantModal">
                <div class="p-6">
                    <h2 class="text-xl font-black text-[var(--text-heading)] flex items-center gap-2 mb-6">
                        <BuildingLibraryIcon class="w-5 h-5 text-primary-dynamic"/>
                        {{ isEditingTenant ? 'Editar Cooperativa' : 'Sancionar Nova Cooperativa' }}
                    </h2>
                    
                    <form @submit.prevent="submitTenant" class="space-y-6">
                        <div>
                            <InputLabel for="nome" value="Nome Oficial ou Razão Social" class="!font-black tracking-widest uppercase text-[10px]" />
                            <TextInput id="nome" type="text" class="block w-full" v-model="tenantForm.nome" required placeholder="Nova Cooperativa Sis" />
                            <InputError class="mt-2" :message="tenantForm.errors.nome" />
                        </div>
                        
                        <div>
                            <InputLabel for="ramo" value="Ramo Comercial / Segmento" class="!font-black tracking-widest uppercase text-[10px]" />
                            <TextInput id="ramo" type="text" class="block w-full" v-model="tenantForm.ramo" placeholder="Saúde, Financeiro, Agro..." />
                            <InputError class="mt-2" :message="tenantForm.errors.ramo" />
                        </div>

                        <!-- Admin Fields (only on create) -->
                        <template v-if="!isEditingTenant">
                            <div class="pt-4 border-t border-[var(--border-color)]">
                                <p class="text-[10px] font-black text-primary-dynamic uppercase tracking-widest mb-4">👤 Administrador Inicial da Cooperativa</p>
                                
                                <div class="space-y-4">
                                    <div>
                                        <InputLabel for="admin_nome" value="Nome do Responsável" class="!font-black tracking-widest uppercase text-[10px]" />
                                        <TextInput id="admin_nome" type="text" class="block w-full" v-model="tenantForm.admin_nome" required placeholder="João da Silva" />
                                        <InputError class="mt-2" :message="tenantForm.errors.admin_nome" />
                                    </div>
                                    
                                    <div>
                                        <InputLabel for="admin_email" value="Email de Acesso" class="!font-black tracking-widest uppercase text-[10px]" />
                                        <TextInput id="admin_email" type="email" class="block w-full" v-model="tenantForm.admin_email" required placeholder="responsavel@cooperativa.com.br" />
                                        <InputError class="mt-2" :message="tenantForm.errors.admin_email" />
                                        <p class="text-[9px] font-medium text-slate-400 mt-1">Uma senha temporária será gerada automaticamente. O usuário deverá alterá-la no primeiro acesso.</p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div class="flex justify-end pt-4 gap-3 border-t border-[var(--border-color)]">
                            <SecondaryButton @click="closeTenantModal">
                                <XMarkIcon class="w-4 h-4 mr-2" /> Cancelar
                            </SecondaryButton>
                            <PrimaryButton :class="{ 'opacity-25': tenantForm.processing }" :disabled="tenantForm.processing">
                                <CheckIcon v-if="isEditingTenant" class="w-4 h-4 mr-2" />
                                <PlusIcon v-else class="w-4 h-4 mr-2" />
                                {{ isEditingTenant ? 'Salvar Configurações' : 'Estruturar Banco de Dados' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>

            <!-- Modal Suspender/Deletar -->
            <Modal :show="isDeleteTenantModalOpen" @close="isDeleteTenantModalOpen = false">
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="p-3 bg-rose-50 dark:bg-rose-500/10 rounded-full text-rose-500">
                            <ExclamationCircleIcon class="w-6 h-6"/>
                        </div>
                        <h2 class="text-xl font-black text-[var(--text-heading)]">Atenção Extrema</h2>
                    </div>

                    <p v-if="currentTenantActionType === 'suspend'" class="text-sm text-slate-500 font-medium mb-6">
                        Você está prestes a <strong class="text-amber-500">Suspender</strong> esta cooperativa. Ela e todos os usuários perderão o acesso ao sistema. O banco de dados permanecerá preservado para o caso de restauração ou auditoria.
                    </p>
                    <p v-else class="text-sm text-slate-500 font-medium mb-6">
                        Você está invocando a flag LGPD para <strong class="text-rose-500">Excluir Permanentemente</strong> esta Cooperativa.<br/><br/>Toda a estrutura do banco de dados relacional (MySQL), dados de dependentes, uploads, faturas e logs serão PURGADOS DEFINITIVAMENTE DO DISCO. Não pode ser revertido. Tem certeza absoluta?
                    </p>

                    <div class="flex justify-end gap-3 pt-6 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="isDeleteTenantModalOpen = false">
                            <XMarkIcon class="w-4 h-4 mr-2" /> Cancelar
                        </SecondaryButton>
                        <DangerButton :class="{ 'opacity-25': tenantForm.processing }" :disabled="tenantForm.processing" @click="deleteTenant">
                            <TrashIcon class="w-4 h-4 mr-2" />
                            {{ currentTenantActionType === 'suspend' ? 'Suspender Operações' : 'Sim, Expurgação Definitiva' }}
                        </DangerButton>
                    </div>
                </div>
            </Modal>

            <!-- Modal Reativar -->
            <Modal :show="isRestoreTenantModalOpen" @close="isRestoreTenantModalOpen = false">
                <div class="p-6">
                    <h2 class="text-xl font-black text-[var(--text-heading)] flex items-center gap-2 mb-6">
                        <CheckCircleIcon class="w-5 h-5 text-emerald-500"/>
                        Reativar Cooperativa
                    </h2>
                    
                    <p class="text-sm text-slate-500 font-medium mb-6">
                        Esta cooperativa foi suspensa. Ao reativá-la, seus associados poderão logar normalmente de onde pararam.
                    </p>

                    <div class="flex justify-end pt-6 gap-3 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="isRestoreTenantModalOpen = false">
                            <XMarkIcon class="w-4 h-4 mr-2" /> Manter Suspenso
                        </SecondaryButton>
                        <PrimaryButton class="!bg-emerald-600 hover:!bg-emerald-700" :class="{ 'opacity-25': tenantForm.processing }" :disabled="tenantForm.processing" @click="restoreTenant">
                            <CheckCircleIcon class="w-4 h-4 mr-2" /> Reativar Serviços
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.zynix-card {
    @apply transition-all duration-300;
}
.zynix-card:hover {
    @apply shadow-lg transform -translate-y-1;
}
.bg-primary-gradient {
    background: linear-gradient(135deg, v-bind(primaryColor), color-mix(in srgb, v-bind(primaryColor) 70%, #1e293b));
}
</style>
