<script setup>
import CooperadoLayout from '@/Layouts/CooperadoLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    CurrencyDollarIcon,
    ClockIcon,
    UserGroupIcon,
    ClipboardDocumentCheckIcon,
    ArrowRightIcon,
    CalendarDaysIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
const associado = computed(() => page.props.associado);
const stats = computed(() => page.props.stats);
const atividadesRecentes = computed(() => page.props.atividadesRecentes || []);
const primaryColor = computed(() => page.props.tenant?.primary_color || '#2563eb');

const greeting = computed(() => {
    const h = new Date().getHours();
    if (h < 12) return 'Bom dia';
    if (h < 18) return 'Boa tarde';
    return 'Boa noite';
});

function formatCurrency(v) {
    return (v || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

const kpis = computed(() => [
    {
        label: 'Cota Capital',
        value: formatCurrency(stats.value.saldoCotaCapital),
        sub: 'Saldo integralizado',
        icon: CurrencyDollarIcon,
        color: 'emerald',
        href: '/cooperado/cotas',
    },
    {
        label: 'Mensalidades',
        value: stats.value.mensalidadesPendentes,
        sub: 'Pendentes de pagamento',
        icon: ClockIcon,
        color: stats.value.mensalidadesPendentes > 0 ? 'amber' : 'emerald',
        href: '/cooperado/financeiro',
    },
    {
        label: 'Dependentes',
        value: stats.value.totalDependentes,
        sub: 'Registrados',
        icon: UserGroupIcon,
        color: 'blue',
        href: '/cooperado/dependentes',
    },
    {
        label: 'Reembolsos',
        value: stats.value.reembolsosEmAnalise,
        sub: 'Em análise',
        icon: ClipboardDocumentCheckIcon,
        color: stats.value.reembolsosEmAnalise > 0 ? 'amber' : 'emerald',
        href: '/cooperado/reembolsos',
    },
]);
</script>

<template>
    <Head title="Meu Painel" />

    <CooperadoLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">
                        {{ greeting }}, {{ $page.props.auth.user.name.split(' ')[0] }}! 👋
                    </h2>
                    <p class="text-[12px] text-[var(--text-muted)] mt-0.5 font-bold">
                        Resumo administrativo das suas atividades e status.
                    </p>
                </div>
                <div class="flex items-center gap-3 bg-[var(--bg-card)] p-2 rounded-lg border border-[var(--border-color)]">
                    <span
                        class="px-3 py-1 rounded text-[10px] font-black uppercase tracking-widest"
                        :class="associado.status === 'ativo'
                            ? 'bg-emerald-50 text-emerald-600'
                            : 'bg-amber-50 text-amber-600'"
                    >
                        {{ associado.status }}
                    </span>
                    <span v-if="associado.data_adesao" class="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wide flex items-center gap-2 border-l border-[var(--border-color)] pl-3">
                        Membro desde {{ associado.data_adesao }}
                    </span>
                </div>
            </div>
        </template>

        <div class="space-y-8 pb-12">
            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <Link
                    v-for="kpi in kpis"
                    :key="kpi.label"
                    :href="kpi.href"
                    class="zynix-card group transition-all hover:border-primary-dynamic/30"
                >
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="w-10 h-10 rounded flex items-center justify-center shadow-sm shrink-0"
                            :class="{
                                'bg-emerald-50 text-emerald-600': kpi.color === 'emerald',
                                'bg-amber-50 text-amber-600': kpi.color === 'amber',
                                'bg-blue-50 text-blue-600': kpi.color === 'blue',
                            }"
                        >
                            <component :is="kpi.icon" class="w-5 h-5 stroke-2" />
                        </div>
                        <span class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-widest">{{ kpi.label }}</span>
                    </div>
                    
                    <h3 class="text-xl font-black text-[var(--text-heading)] tracking-tight">
                        {{ kpi.value }}
                    </h3>
                    <p class="text-[10px] font-bold text-[var(--text-muted)] mt-1 uppercase tracking-wide">{{ kpi.sub }}</p>
                    
                    <div class="mt-4 pt-4 border-t border-[var(--border-color)] flex items-center justify-between text-primary-dynamic">
                        <span class="text-[9px] font-black uppercase tracking-widest">Detalhes</span>
                        <ArrowRightIcon class="w-4 h-4" />
                    </div>
                </Link>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Activity Timeline -->
                <div class="lg:col-span-2">
                    <div class="zynix-card h-full">
                        <div class="zynix-card-header mb-6">
                            <h4 class="text-[11px] font-black text-[var(--text-heading)] uppercase tracking-widest">Atividades Recentes</h4>
                        </div>

                        <div v-if="atividadesRecentes.length" class="space-y-4">
                            <div
                                v-for="(atividade, idx) in atividadesRecentes"
                                :key="atividade.id"
                                class="flex items-center gap-4 p-4 rounded-lg bg-slate-50 dark:bg-white/[0.03] border border-transparent hover:border-[var(--border-color)] transition-all"
                            >
                                <!-- Status Indicator -->
                                <div class="w-2.5 h-2.5 rounded-full shrink-0"
                                    :class="{
                                        'bg-emerald-500': atividade.status === 'pago',
                                        'bg-amber-500': atividade.status === 'pendente',
                                        'bg-rose-500': atividade.status === 'atrasado',
                                    }"
                                ></div>

                                <!-- Content -->
                                <div class="flex-1 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div>
                                        <p class="text-xs font-black text-[var(--text-heading)] uppercase tracking-tight">
                                            {{ atividade.tipo === 'entrada' ? 'Pagamento' : 'Cobrança' }}
                                        </p>
                                        <p class="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wide">
                                            Vencimento: {{ atividade.data_vencimento }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span class="text-sm font-black" :class="atividade.tipo === 'entrada' ? 'text-emerald-600' : 'text-rose-600'">
                                            {{ formatCurrency(atividade.valor) }}
                                        </span>
                                        <span
                                            class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest"
                                            :class="{
                                                'bg-emerald-500/10 text-emerald-600': atividade.status === 'pago',
                                                'bg-amber-500/10 text-amber-600': atividade.status === 'pendente',
                                                'bg-rose-500/10 text-rose-600': atividade.status === 'atrasado',
                                            }"
                                        >
                                            {{ atividade.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="py-12 text-center">
                            <ClockIcon class="w-12 h-12 mx-auto text-slate-200 mb-3" />
                            <p class="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest">Nenhuma movimentação</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Sidebar -->
                <div class="space-y-6">
                    <!-- Status Card -->
                    <div class="zynix-card bg-slate-900 text-white relative overflow-hidden group">
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Cooperado</h4>
                                <CheckCircleIcon class="w-5 h-5 text-emerald-400" />
                            </div>
                            <p class="text-lg font-black tracking-tight truncate">{{ associado.nome }}</p>
                            <div class="mt-6 flex items-center justify-between border-t border-slate-800 pt-4">
                                <div>
                                    <p class="text-[8px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1">Matrícula</p>
                                    <p class="text-xs font-black">#{{ associado.id.toString().padStart(4, '0') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[8px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1">Status</p>
                                    <span class="text-[10px] font-black text-emerald-400 uppercase tracking-widest">{{ associado.status }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="zynix-card p-0 overflow-hidden shadow-none border-[var(--border-color)]">
                        <div class="p-4 border-b border-[var(--border-color)] bg-slate-50 dark:bg-white/[0.03]">
                            <h4 class="text-[10px] font-black text-[var(--text-heading)] uppercase tracking-widest">Ações Operacionais</h4>
                        </div>
                        <div class="divide-y divide-[var(--border-color)]">
                            <Link href="/cooperado/reembolsos" class="flex items-center gap-3 p-4 hover:bg-slate-50 dark:hover:bg-white/[0.03] transition-all group">
                                <div class="w-8 h-8 rounded bg-primary-light flex items-center justify-center text-primary-dynamic">
                                    <ClipboardDocumentCheckIcon class="w-4 h-4" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-black text-[var(--text-heading)] uppercase tracking-tight">Solicitar Reembolso</p>
                                    <p class="text-[9px] text-[var(--text-muted)] font-bold uppercase tracking-wide">Envio de solicitação</p>
                                </div>
                                <ArrowRightIcon class="w-4 h-4 text-[var(--text-muted)] group-hover:text-primary-dynamic transition-all" />
                            </Link>
                            <Link href="/cooperado/financeiro" class="flex items-center gap-3 p-4 hover:bg-slate-50 dark:hover:bg-white/[0.03] transition-all group">
                                <div class="w-8 h-8 rounded bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                    <CurrencyDollarIcon class="w-4 h-4" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-black text-[var(--text-heading)] uppercase tracking-tight">Financeiro / Extrato</p>
                                    <p class="text-[9px] text-[var(--text-muted)] font-bold uppercase tracking-wide">Lançamentos e faturas</p>
                                </div>
                                <ArrowRightIcon class="w-4 h-4 text-[var(--text-muted)] group-hover:text-primary-dynamic transition-all" />
                            </Link>
                        </div>
                    </div>

                    <!-- Simplified Alert Card -->
                    <div v-if="stats.mensalidadesPendentes > 0" class="zynix-card !border-amber-300 bg-amber-50 shadow-none">
                        <div class="flex items-start gap-4">
                            <ExclamationTriangleIcon class="w-5 h-5 text-amber-500 shrink-0" />
                            <div>
                                <p class="text-[11px] font-black text-amber-900 uppercase tracking-widest">Pagamento Pendente</p>
                                <p class="text-[10px] font-bold text-amber-700 mt-2 leading-relaxed uppercase tracking-wide">
                                    Regularize suas pendências para manter o acesso completo aos benefícios.
                                </p>
                                <Link href="/cooperado/financeiro" class="inline-block mt-3 text-[10px] font-black text-amber-800 hover:text-amber-950 underline uppercase tracking-widest">Regularizar Agora</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CooperadoLayout>
</template>
<style scoped>
.bg-primary-zynix {
    background: linear-gradient(135deg, v-bind(primaryColor), #1e293b);
}
</style>
