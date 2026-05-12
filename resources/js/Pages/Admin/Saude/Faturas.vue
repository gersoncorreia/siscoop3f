<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import {
    CheckCircleIcon,
    DocumentTextIcon,
    BanknotesIcon,
    ClockIcon,
    BuildingStorefrontIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    faturas: Array
});

const form = useForm({});

const auditar = (id) => {
    form.post(route('saude.faturas.auditar', id), { preserveScroll: true });
};

const pagar = (id) => {
    form.post(route('saude.faturas.pagar', id), { preserveScroll: true });
};

const formatCurrency = (value) => {
    if (!value) return '-';
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};
</script>

<template>
    <Head title="Faturas da Rede Credenciada" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">Faturas - Dia 05</h2>
                    <p class="text-sm text-[var(--text-muted)] mt-1 font-medium">Controle de pagamentos da rede credenciada e parceiros.</p>
                </div>
                <div class="p-3 bg-[var(--bg-card)] rounded-xl border border-[var(--border-color)]">
                    <BanknotesIcon class="w-6 h-6 text-primary-dynamic" />
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div v-if="$page.props.flash?.success" class="bg-emerald-50 text-emerald-700 p-4 rounded-xl border border-emerald-100 flex items-center">
                    <CheckCircleIcon class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span class="text-sm font-bold">{{ $page.props.flash.success }}</span>
                </div>

                <div class="bg-[var(--bg-card)] overflow-hidden shadow-sm rounded-2xl border border-[var(--border-color)]">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[var(--border-color)]">
                            <thead class="bg-slate-50/50 dark:bg-slate-800/50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Prestador</th>
                                    <th scope="col" class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Referência</th>
                                    <th scope="col" class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Valor</th>
                                    <th scope="col" class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                                    <th scope="col" class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--border-color)] bg-transparent">
                                <tr v-for="fat in faturas" :key="fat.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center border border-slate-200 dark:border-slate-700">
                                                <BuildingStorefrontIcon class="h-5 w-5 text-slate-500" />
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-black text-[var(--text-heading)]">{{ fat.prestador }}</div>
                                                <div class="text-xs text-[var(--text-muted)] font-bold mt-0.5">Envio: {{ fat.data_envio }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-black text-[var(--text-heading)]">{{ fat.mes_referencia }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-black text-emerald-600 dark:text-emerald-400">{{ formatCurrency(fat.valor_total) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-if="fat.status === 'enviada'" class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-amber-100 text-amber-800">
                                            <ClockIcon class="w-3 h-3 mr-1" /> Recebida
                                        </span>
                                        <span v-else-if="fat.status === 'em_auditoria'" class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-blue-100 text-blue-800">
                                            <DocumentTextIcon class="w-3 h-3 mr-1" /> Em Auditoria
                                        </span>
                                        <span v-else-if="fat.status === 'paga'" class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-800">
                                            <CheckCircleIcon class="w-3 h-3 mr-1" /> Paga ({{ fat.data_pagamento }})
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <a v-if="fat.arquivo_guia" href="#" class="text-xs font-bold text-slate-500 hover:text-slate-700 mr-2">
                                                Ver Anexo
                                            </a>
                                            <button v-if="fat.status === 'enviada'" @click="auditar(fat.id)" class="text-blue-600 bg-blue-50 px-3 py-1.5 rounded-lg text-xs font-black">
                                                Auditar
                                            </button>
                                            <button v-if="fat.status === 'em_auditoria'" @click="pagar(fat.id)" class="text-white bg-emerald-500 hover:bg-emerald-600 px-4 py-1.5 rounded-lg text-xs font-black">
                                                Marcar como Paga
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="faturas.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                        Nenhuma fatura pendente ou enviada no momento.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
