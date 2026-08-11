<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { Head } from '@inertiajs/vue3';
import { 
    UsersIcon, 
    UserPlusIcon, 
    UserMinusIcon,
    CalendarDaysIcon,
    CurrencyDollarIcon,
    ChartBarIcon,
    HeartIcon
} from '@heroicons/vue/24/outline';

// Chart.js Setup
import { Line, Bar } from 'vue-chartjs';
import { 
    Chart as ChartJS, 
    Title, 
    Tooltip, 
    Legend, 
    LineElement, 
    BarElement, 
    CategoryScale, 
    LinearScale, 
    PointElement, 
    Filler 
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, LineElement, BarElement, CategoryScale, LinearScale, PointElement, Filler);

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    evolucaoMembros: { type: Array, default: () => [] },
    receitaPorCategoria: { type: Array, default: () => [] },
    topServicos: { type: Array, default: () => [] },
});

// Chart Data mapping
const lineChartData = {
    labels: props.evolucaoMembros.map(e => e.label),
    datasets: [
        {
            label: 'Total Membros',
            data: props.evolucaoMembros.map(e => e.total),
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            fill: true,
            tension: 0.4
        },
        {
            label: 'Novos',
            data: props.evolucaoMembros.map(e => e.novos),
            borderColor: '#10b981',
            backgroundColor: 'transparent',
            tension: 0.4
        }
    ]
};

const barChartData = {
    labels: props.receitaPorCategoria.map(r => r.categoria),
    datasets: [{
        label: 'Receita (R$)',
        data: props.receitaPorCategoria.map(r => r.total),
        backgroundColor: '#6366f1',
        borderRadius: 4
    }]
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom', labels: { font: { size: 10, weight: 'bold' } } }
    },
    scales: {
        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
        x: { grid: { display: false } }
    }
};

const formatCurrency = (val) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val || 0);
</script>

<template>
    <Head title="Indicadores de Desempenho" />

    <AuthenticatedLayout>
        <template #header>
            <Breadcrumbs :items="[{ label: 'Admin' }, { label: 'Indicadores e Dashboard' }]" />
            <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Indicadores de Desempenho</h2>
            <p class="text-sm text-[var(--text-muted)] mt-0.5">Visão geral e métricas de saúde, financeiros e membros.</p>
        </template>

        <div class="pb-12 space-y-6">
            <!-- Row 1: Membros -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="zynix-card p-5 flex items-center gap-4">
                    <div class="p-3 bg-indigo-50 text-indigo-500 rounded-xl dark:bg-indigo-500/10"><UsersIcon class="w-6 h-6" /></div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Membros</p>
                        <p class="text-2xl font-black text-[var(--text-heading)]">{{ stats.total_membros || 0 }}</p>
                    </div>
                </div>
                <div class="zynix-card p-5 flex items-center gap-4">
                    <div class="p-3 bg-emerald-50 text-emerald-500 rounded-xl dark:bg-emerald-500/10"><HeartIcon class="w-6 h-6" /></div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Membros Ativos</p>
                        <p class="text-2xl font-black text-[var(--text-heading)]">{{ stats.membros_ativos || 0 }}</p>
                    </div>
                </div>
                <div class="zynix-card p-5 flex items-center gap-4">
                    <div class="p-3 bg-blue-50 text-blue-500 rounded-xl dark:bg-blue-500/10"><UserPlusIcon class="w-6 h-6" /></div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Adesões (Mês)</p>
                        <p class="text-2xl font-black text-[var(--text-heading)]">{{ stats.novas_adesoes || 0 }}</p>
                    </div>
                </div>
                <div class="zynix-card p-5 flex items-center gap-4">
                    <div class="p-3 bg-rose-50 text-rose-500 rounded-xl dark:bg-rose-500/10"><UserMinusIcon class="w-6 h-6" /></div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Cancelamentos (Mês)</p>
                        <p class="text-2xl font-black text-[var(--text-heading)]">{{ stats.cancelamentos || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Row 2: Saúde / Atendimentos -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="zynix-card p-5">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Agendamentos Hoje</p>
                    <p class="text-xl font-black text-[var(--text-heading)]">{{ stats.agendamentos_hoje || 0 }}</p>
                </div>
                <div class="zynix-card p-5">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Agendamentos Mês</p>
                    <p class="text-xl font-black text-[var(--text-heading)]">{{ stats.agendamentos_mes || 0 }}</p>
                </div>
                <div class="zynix-card p-5">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Taxa Atendimento</p>
                    <p class="text-xl font-black text-emerald-500">{{ stats.taxa_atendimento || 0 }}%</p>
                </div>
                <div class="zynix-card p-5">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Internações Análise</p>
                    <p class="text-xl font-black text-amber-500">{{ stats.internacoes_analise || 0 }}</p>
                </div>
            </div>

            <!-- Row 3: Financeiro -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="zynix-card p-5 border-l-4 border-l-emerald-500">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Receita Mês</p>
                    <p class="text-xl font-black text-[var(--text-heading)]">{{ formatCurrency(stats.receita_mes) }}</p>
                </div>
                <div class="zynix-card p-5 border-l-4 border-l-rose-500">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Despesa Mês</p>
                    <p class="text-xl font-black text-[var(--text-heading)]">{{ formatCurrency(stats.despesa_mes) }}</p>
                </div>
                <div class="zynix-card p-5 border-l-4 border-l-blue-500">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Saldo Mês</p>
                    <p class="text-xl font-black text-[var(--text-heading)]">{{ formatCurrency(stats.saldo_mes) }}</p>
                </div>
                <div class="zynix-card p-5 border-l-4 border-l-indigo-500">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Campanhas Ativas</p>
                    <p class="text-xl font-black text-[var(--text-heading)]">{{ stats.campanhas_ativas || 0 }}</p>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Evolução de Membros -->
                <div class="zynix-card p-6">
                    <h3 class="text-sm font-black text-[var(--text-heading)] uppercase tracking-widest mb-4">Evolução de Membros (6 meses)</h3>
                    <div class="h-64">
                        <Line :data="lineChartData" :options="chartOptions" />
                    </div>
                </div>
                
                <!-- Receita por Categoria -->
                <div class="zynix-card p-6">
                    <h3 class="text-sm font-black text-[var(--text-heading)] uppercase tracking-widest mb-4">Receita por Categoria</h3>
                    <div class="h-64">
                        <Bar :data="barChartData" :options="chartOptions" />
                    </div>
                </div>
            </div>

            <!-- Top Serviços Table -->
            <div class="zynix-card overflow-hidden !p-0">
                <div class="p-5 border-b border-[var(--border-color)]">
                    <h3 class="text-sm font-black text-[var(--text-heading)] uppercase tracking-widest">Top 10 Serviços Mais Utilizados</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="zynix-table w-full text-left">
                        <thead>
                            <tr>
                                <th class="p-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Serviço</th>
                                <th class="p-4 text-[9px] font-black uppercase tracking-widest text-slate-400 text-center">Total Agendamentos</th>
                                <th class="p-4 text-[9px] font-black uppercase tracking-widest text-slate-400 text-right">Receita Gerada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(s, idx) in topServicos" :key="idx" class="border-b border-[var(--border-color)]">
                                <td class="p-4 font-bold text-sm text-[var(--text-heading)]">
                                    <span class="text-slate-400 mr-2">#{{ idx + 1 }}</span> {{ s.nome }}
                                </td>
                                <td class="p-4 text-center font-bold text-slate-600">{{ s.total_agendamentos }}</td>
                                <td class="p-4 text-right font-black text-emerald-600">{{ formatCurrency(s.receita) }}</td>
                            </tr>
                            <tr v-if="!topServicos || topServicos.length === 0">
                                <td colspan="3" class="p-8 text-center text-xs font-bold text-slate-400 uppercase tracking-widest">
                                    Dados insuficientes para gerar o ranking.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
