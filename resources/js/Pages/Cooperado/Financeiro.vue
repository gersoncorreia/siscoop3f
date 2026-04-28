<script setup>
import CooperadoLayout from '@/Layouts/CooperadoLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { BanknotesIcon, FunnelIcon } from '@heroicons/vue/24/outline';

const page = usePage();
const lancamentos = computed(() => page.props.lancamentos);
const filtroStatus = computed(() => page.props.filtroStatus || '');

const statusFilter = ref(filtroStatus.value);

function formatCurrency(v) {
    return (v || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function applyFilter() {
    router.get('/cooperado/financeiro', statusFilter.value ? { status: statusFilter.value } : {}, {
        preserveState: true,
        preserveScroll: true,
    });
}

function clearFilter() {
    statusFilter.value = '';
    router.get('/cooperado/financeiro', {}, { preserveState: true });
}
</script>

<template>
    <Head title="Meu Financeiro" />
    <CooperadoLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">Meu Financeiro</h2>
                    <p class="text-sm text-[var(--text-muted)] mt-1 font-medium">Extrato de lançamentos e cobranças.</p>
                </div>
                <div class="flex items-center gap-2">
                    <select
                        v-model="statusFilter"
                        @change="applyFilter"
                        class="text-xs font-bold px-4 py-2 rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-heading)] focus:ring-1 focus:ring-primary-dynamic focus:border-primary-dynamic transition-all"
                    >
                        <option value="">Todos os Status</option>
                        <option value="pago">Pago</option>
                        <option value="pendente">Pendente</option>
                        <option value="atrasado">Atrasado</option>
                    </select>
                    <button v-if="filtroStatus" @click="clearFilter" class="text-[10px] font-black text-rose-500 uppercase tracking-widest hover:underline">Limpar</button>
                </div>
            </div>
        </template>

        <div class="zynix-card !p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="zynix-table">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Vencimento</th>
                            <th>Data Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="lanc in lancamentos.data" :key="lanc.id" class="group">
                            <td>
                                <div class="flex items-center gap-2">
                                    <div
                                        class="p-1.5 rounded-lg"
                                        :class="lanc.tipo === 'entrada' ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600' : 'bg-rose-50 dark:bg-rose-500/10 text-rose-600'"
                                    >
                                        <BanknotesIcon class="w-4 h-4" />
                                    </div>
                                    <span class="text-xs font-black uppercase tracking-wider" :class="lanc.tipo === 'entrada' ? 'text-emerald-600' : 'text-rose-600'">
                                        {{ lanc.tipo }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm font-black" :class="lanc.tipo === 'entrada' ? 'text-emerald-500' : 'text-[var(--text-heading)]'">
                                    {{ formatCurrency(lanc.valor) }}
                                </span>
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
                            <td class="text-xs font-bold text-[var(--text-muted)]">{{ lanc.data_vencimento }}</td>
                            <td class="text-xs font-bold text-[var(--text-muted)]">{{ lanc.created_at }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="!lancamentos.data.length" class="py-16 text-center">
                <BanknotesIcon class="w-12 h-12 mx-auto text-slate-200 dark:text-slate-700 mb-3" />
                <p class="text-xs font-bold text-[var(--text-muted)] uppercase tracking-widest">Nenhum lançamento encontrado</p>
            </div>

            <!-- Pagination -->
            <div v-if="lancamentos.last_page > 1" class="flex items-center justify-between p-4 border-t border-[var(--border-color)]">
                <p class="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider">
                    Mostrando {{ lancamentos.from }}-{{ lancamentos.to }} de {{ lancamentos.total }}
                </p>
                <div class="flex gap-1">
                    <Link
                        v-for="link in lancamentos.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        class="px-3 py-1.5 text-[10px] font-black rounded-lg transition-all"
                        :class="link.active
                            ? 'bg-primary-dynamic text-white'
                            : link.url
                                ? 'text-[var(--text-muted)] hover:bg-slate-100 dark:hover:bg-slate-800'
                                : 'text-slate-300 dark:text-slate-600 cursor-not-allowed'"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </CooperadoLayout>
</template>
