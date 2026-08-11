<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    BuildingLibraryIcon,
} from '@heroicons/vue/24/outline';

import Breadcrumbs from '@/Components/Breadcrumbs.vue';

const page = usePage();
const kpis = computed(() => page.props.kpis || {});
const tenant = computed(() => page.props.tenant || null);

function formatCurrency(v) {
    return (Number(v) || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}
</script>

<template>
    <Head title="Dashboard Financeiro" />

    <AuthenticatedLayout>
        <template #header>
            <Breadcrumbs />
            <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Painel Financeiro</h2>
            <p class="text-sm text-[var(--text-muted)] mt-0.5">Acompanhe entradas, saídas e faturamento consolidado.</p>
        </template>

        <div class="space-y-6 pb-12">

                <!-- Welcome Banner -->
                <div class="zynix-card overflow-hidden relative border-none bg-gradient-to-r from-primary-dynamic/10 to-transparent">
                    <div class="absolute top-0 right-0 w-64 h-64 rounded-full opacity-10 -translate-y-1/2 translate-x-1/4 bg-primary-dynamic" style="filter: blur(60px);"></div>
                    <div class="relative">
                        <h3 class="text-xl font-black text-[var(--text-heading)] tracking-tight">
                            Resultados — <span class="text-primary-dynamic uppercase">{{ tenant?.nome || tenant?.id }}</span>
                        </h3>
                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-1">
                            Acompanhamento consolidado de receitas, despesas e fluxo de caixa.
                        </p>
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Receitas do Mês -->
                    <div class="zynix-card group hover:scale-[1.02] transition-all duration-300">
                        <div class="flex items-center gap-4">
                            <div class="p-4 rounded-2xl bg-emerald-500/10 text-emerald-500 shadow-inner group-hover:bg-emerald-500/20 transition-colors">
                                <ArrowTrendingUpIcon class="w-8 h-8" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Receitas / Mês</p>
                                <p class="text-2xl font-black text-emerald-500 tracking-tighter mt-1">{{ formatCurrency(kpis.receitasMes) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Despesas do Mês -->
                    <div class="zynix-card group hover:scale-[1.02] transition-all duration-300">
                        <div class="flex items-center gap-4">
                            <div class="p-4 rounded-2xl bg-rose-500/10 text-rose-500 shadow-inner group-hover:bg-rose-500/20 transition-colors">
                                <ArrowTrendingDownIcon class="w-8 h-8" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Despesas / Mês</p>
                                <p class="text-2xl font-black text-rose-500 tracking-tighter mt-1">{{ formatCurrency(kpis.despesasMes) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Saldo Total -->
                    <div class="zynix-card group hover:scale-[1.02] transition-all duration-300 border-primary-dynamic/20">
                        <div class="flex items-center gap-4">
                            <div class="p-4 rounded-2xl bg-primary-dynamic/10 text-primary-dynamic shadow-inner group-hover:bg-primary-dynamic/20 transition-colors">
                                <BuildingLibraryIcon class="w-8 h-8" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Saldo Consolidado</p>
                                <p class="text-2xl font-black text-[var(--text-heading)] tracking-tighter mt-1">{{ formatCurrency(kpis.saldoTotal) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Sections -->
                <div class="zynix-card overflow-hidden text-center py-16 border-dashed border-2">
                    <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <BuildingLibraryIcon class="w-8 h-8 text-slate-300" />
                    </div>
                    <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">Módulo Financeiro Avançado</h3>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-2 max-w-sm mx-auto leading-relaxed">
                        Em breve: Conciliação automática, gestão de capital social e integração bancária completa.
                    </p>
                </div>

        </div>
    </AuthenticatedLayout>
</template>
