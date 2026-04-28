<script setup>
import CooperadoLayout from '@/Layouts/CooperadoLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { CurrencyDollarIcon, ArrowTrendingUpIcon, ArrowTrendingDownIcon } from '@heroicons/vue/24/outline';

const page = usePage();
const cotas = computed(() => page.props.cotas || []);
const saldoTotal = computed(() => page.props.saldoTotal || 0);
const primaryColor = computed(() => page.props.tenant?.primary_color || '#2563eb');

function formatCurrency(v) {
    return (v || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function tipoLabel(tipo) {
    const labels = {
        integralizacao_inicial: 'Integralização Inicial',
        integralizacao: 'Integralização',
        devolucao: 'Devolução',
        correcao: 'Correção Monetária',
    };
    return labels[tipo] || tipo;
}
</script>

<template>
    <Head title="Cotas Capital" />
    <CooperadoLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">Cotas Capital</h2>
                <p class="text-sm text-[var(--text-muted)] mt-1 font-medium">Saldo e histórico de movimentações de cota.</p>
            </div>
        </template>

        <div class="space-y-8">
            <!-- Saldo Total Card -->
            <div class="zynix-card bg-primary-gradient !text-white !shadow-xl relative overflow-hidden">
                <div class="relative z-10 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-white/70 mb-2">Saldo Total Integralizado</p>
                        <p class="text-4xl font-black tracking-tighter">{{ formatCurrency(saldoTotal) }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-white/10">
                        <CurrencyDollarIcon class="w-8 h-8 text-white" />
                    </div>
                </div>
                <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-white/5 rounded-full blur-xl"></div>
            </div>

            <!-- History Table -->
            <div class="zynix-card !p-0 overflow-hidden">
                <div class="p-5 border-b border-[var(--border-color)]">
                    <h4 class="text-sm font-black text-[var(--text-heading)] uppercase tracking-widest">Histórico de Movimentações</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="zynix-table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Valor Cota</th>
                                <th>Qtd</th>
                                <th>Saldo</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="cota in cotas" :key="cota.id">
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="p-1.5 rounded-lg" :class="cota.tipo_movimento.includes('devolucao') ? 'bg-rose-50 dark:bg-rose-500/10 text-rose-600' : 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600'">
                                            <ArrowTrendingUpIcon v-if="!cota.tipo_movimento.includes('devolucao')" class="w-4 h-4" />
                                            <ArrowTrendingDownIcon v-else class="w-4 h-4" />
                                        </div>
                                        <span class="text-xs font-bold text-[var(--text-heading)]">{{ tipoLabel(cota.tipo_movimento) }}</span>
                                    </div>
                                </td>
                                <td class="text-sm font-bold text-[var(--text-heading)]">{{ formatCurrency(cota.valor_quota) }}</td>
                                <td class="text-sm font-black text-[var(--text-heading)]">{{ cota.qtd_quotas }}</td>
                                <td class="text-sm font-black text-emerald-600 dark:text-emerald-400">{{ formatCurrency(cota.saldo) }}</td>
                                <td class="text-xs font-bold text-[var(--text-muted)]">{{ cota.created_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="!cotas.length" class="py-16 text-center">
                    <CurrencyDollarIcon class="w-12 h-12 mx-auto text-slate-200 dark:text-slate-700 mb-3" />
                    <p class="text-xs font-bold text-[var(--text-muted)] uppercase tracking-widest">Nenhuma movimentação de cota registrada</p>
                </div>
            </div>
        </div>
    </CooperadoLayout>
</template>

<style scoped>
.bg-primary-gradient {
    background: linear-gradient(135deg, v-bind(primaryColor), color-mix(in srgb, v-bind(primaryColor) 70%, #1e293b));
}
</style>
