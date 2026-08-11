<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { 
    BanknotesIcon,
    CheckBadgeIcon,
    CalendarDaysIcon,
    UserIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    lancamentos: Array
});

const form = useForm({});

function conciliar(id) {
    if (confirm('Deseja marcar este lançamento como PAGO / CONCILIADO?')) {
        form.post(route('financeiro.conciliar', id), {
            preserveScroll: true
        });
    }
}
</script>

<template>
    <Head title="Conciliação Financeira" />

    <AuthenticatedLayout>
        <template #header>
            <Breadcrumbs :items="[{ label: 'Financeiro', href: '/financeiro' }, { label: 'Conciliação' }]" />
            <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Conciliação Bancária</h2>
            <p class="text-sm text-[var(--text-muted)] mt-0.5">Conforme e valide os extratos bancários com os lançamentos da unidade.</p>
        </template>

        <div class="space-y-6 pb-12">
            <div v-if="$page.props.flash?.success" class="p-4 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20 rounded-2xl font-bold flex items-center gap-3">
                <CheckBadgeIcon class="w-5 h-5" />
                {{ $page.props.flash.success }}
            </div>

                <div class="zynix-card overflow-hidden !p-0">
                    <div class="p-6 border-b border-[var(--border-color)]">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-primary-dynamic/10 text-primary-dynamic rounded-xl shadow-inner">
                                <BanknotesIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="font-black text-[var(--text-heading)] leading-tight tracking-tight">Lançamentos Pendentes</h3>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">Marque os itens à medida que os identificar no seu extrato</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="zynix-table w-full">
                            <thead>
                                <tr>
                                    <th class="pl-8">Vencimento</th>
                                    <th>Associado</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-right">Valor</th>
                                    <th class="text-right pr-8">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in lancamentos" :key="item.id">
                                    <td class="pl-8">
                                        <div class="flex items-center text-[var(--text-heading)] font-black">
                                            <CalendarDaysIcon class="w-4 h-4 mr-2 text-slate-400" />
                                            {{ new Date(item.data_vencimento).toLocaleDateString('pt-BR') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center">
                                            <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mr-3">
                                                <UserIcon class="w-4 h-4" />
                                            </div>
                                            <div class="font-bold text-slate-600 dark:text-slate-400 italic">
                                                {{ item.associado?.nome || 'Lançamento Geral' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span 
                                            :class="item.tipo === 'receita' ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10 border-emerald-100 dark:border-emerald-500/20' : 'text-rose-600 bg-rose-50 dark:bg-rose-500/10 border-rose-100 dark:border-rose-500/20'"
                                            class="px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border inline-flex items-center gap-1.5"
                                        >
                                            <span 
                                                class="w-1.5 h-1.5 rounded-full shadow-sm"
                                                :class="item.tipo === 'receita' ? 'bg-emerald-500' : 'bg-rose-500'"
                                            ></span>
                                            {{ item.tipo }}
                                        </span>
                                    </td>
                                    <td class="text-right font-black text-[var(--text-heading)] tabular-nums">
                                        <span class="text-[10px] text-slate-400 font-bold mr-1 italic">R$</span>
                                        {{ parseFloat(item.valor).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                                    </td>
                                    <td class="text-right pr-8">
                                        <PrimaryButton @click="conciliar(item.id)" class="!py-2 !px-4 !text-[10px] !rounded-xl shadow-lg shadow-emerald-500/10">
                                            <CheckBadgeIcon class="w-3.5 h-3.5 mr-1.5" />
                                            Confirmar
                                        </PrimaryButton>
                                    </td>
                                </tr>
                                <tr v-if="lancamentos.length === 0">
                                    <td colspan="5" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="p-4 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-300 mb-4 animate-pulse">
                                                <ArrowPathIcon class="w-8 h-8" />
                                            </div>
                                            <p class="font-black text-slate-400 uppercase tracking-widest text-xs">Tudo Conciliado! 🎉</p>
                                            <p class="text-[10px] font-bold text-slate-400 mt-2">Nenhum lançamento pendente encontrado.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
        </div>
    </AuthenticatedLayout>
</template>
