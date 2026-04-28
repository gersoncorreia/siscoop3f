<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { 
    PlusIcon, 
    ArrowUpCircleIcon, 
    ArrowDownCircleIcon,
    CalendarDaysIcon,
    FunnelIcon,
    TrashIcon,
    PencilSquareIcon,
    BanknotesIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    lancamentos: Object,
    associados: Array,
});

const showModal = ref(false);
const form = useForm({
    associado_id: '',
    tipo: 'receita',
    valor: 0,
    data_vencimento: new Date().toISOString().split('T')[0],
    status: 'pendente',
});

const submit = () => {
    form.post(route('financeiro.lancamentos.store'), {
        onSuccess: () => { showModal.value = false; form.reset(); }
    });
};

const deleteLancamento = (id) => {
    if (confirm('Excluir este lançamento?')) {
        form.delete(route('financeiro.lancamentos.destroy', id));
    }
};
</script>

<template>
    <Head title="Fluxo de Caixa" />

    <AuthenticatedLayout>
        <div class="py-6 sm:py-8 lg:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 lg:mb-12">
                    <div>
                        <Breadcrumbs :items="[{ label: 'Financeiro', href: '/financeiro' }, { label: 'Lançamentos' }]" />
                        <h1 class="text-2xl lg:text-3xl font-black text-[var(--text-heading)] tracking-tighter mt-2">
                            Fluxo de Caixa
                        </h1>
                    </div>
                    <div>
                        <PrimaryButton @click="showModal = true" class="shadow-xl !px-8 !py-3">
                            <PlusIcon class="w-4 h-4 mr-2" />
                            Novo Lançamento
                        </PrimaryButton>
                    </div>
                </div>
                <!-- Transactions Table -->
                <div class="zynix-table-wrapper">
                    <div class="zynix-list-header flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <span class="text-sm font-bold text-slate-500 uppercase flex items-center">
                                <FunnelIcon class="w-4 h-4 mr-2" />
                                Filtros Rápidos:
                            </span>
                            <button class="px-5 py-1.5 bg-[var(--bg-card)] border border-[var(--border-color)] rounded-full text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] hover:border-primary-dynamic hover:text-primary-dynamic transition-all">Todo Período</button>
                            <button class="px-5 py-1.5 bg-[var(--bg-card)] border border-[var(--border-color)] rounded-full text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] hover:border-primary-dynamic hover:text-primary-dynamic transition-all">Receitas</button>
                            <button class="px-5 py-1.5 bg-[var(--bg-card)] border border-[var(--border-color)] rounded-full text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] hover:border-primary-dynamic hover:text-primary-dynamic transition-all">Despesas</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 dark:bg-slate-800/80 text-[var(--text-muted)] font-bold uppercase text-[10px] tracking-widest border-b border-[var(--border-color)]">
                                <tr>
                                    <th class="px-6 py-4">Data Venc.</th>
                                    <th class="px-6 py-4">Associado / Descrição</th>
                                    <th class="px-6 py-4 text-center">Tipo</th>
                                    <th class="px-6 py-4 text-right">Valor</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--border-color)]/20 zynix-table">
                                <tr v-for="item in lancamentos.data" :key="item.id" class="transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap text-[var(--text-main)] font-medium">
                                        <div class="flex items-center">
                                            <CalendarDaysIcon class="w-4 h-4 mr-2 text-slate-300" />
                                            {{ new Date(item.data_vencimento).toLocaleDateString('pt-BR') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-[var(--text-heading)]">{{ item.associado ? item.associado.nome : 'Lançamento Geral' }}</div>
                                        <div class="text-[10px] text-[var(--text-muted)] uppercase tracking-tighter">REF: #{{ item.id }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span v-if="item.tipo === 'receita'" class="inline-flex items-center text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-1 rounded-full text-[10px] font-black uppercase">
                                            <ArrowUpCircleIcon class="w-3.5 h-3.5 mr-1.5" /> Entrada
                                        </span>
                                        <span v-else class="inline-flex items-center text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-500/10 px-2.5 py-1 rounded-full text-[10px] font-black uppercase">
                                            <ArrowDownCircleIcon class="w-3.5 h-3.5 mr-1.5" /> Saída
                                        </span>
                                    </td>
                                    <td :class="item.tipo === 'receita' ? 'text-emerald-700' : 'text-rose-700'" class="px-6 py-4 text-right font-black tabular-nums">
                                        {{ item.tipo === 'receita' ? '+' : '-' }} R$ {{ parseFloat(item.valor).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span 
                                            :class="{
                                                'text-emerald-600 bg-emerald-50 border-emerald-100': item.status === 'pago',
                                                'text-amber-600 bg-amber-50 border-amber-100': item.status === 'pendente',
                                                'text-rose-600 bg-rose-50 border-rose-100': item.status === 'atrasado'
                                            }"
                                            class="px-2 py-1 rounded-full text-[10px] font-bold uppercase border"
                                        >
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="deleteLancamento(item.id)" class="p-2 text-slate-300 dark:text-slate-600 hover:text-rose-600 transition-all rounded-full hover:bg-rose-50 dark:hover:bg-rose-500/10">
                                            <TrashIcon class="w-5 h-5" />
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="lancamentos.data.length === 0">
                                    <td colspan="6" class="px-6 py-24 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="p-6 rounded-full bg-primary-light mb-6">
                                                <BanknotesIcon class="w-10 h-10" />
                                            </div>
                                            <p class="font-black text-xl text-[var(--text-heading)] tracking-tighter">Nenhum lançamento registrado</p>
                                            <p class="text-xs text-[var(--text-muted)] mt-2 uppercase tracking-widest font-black opacity-50">Clique em "Novo Lançamento" para começar.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Modal -->
        <Modal :show="showModal" @close="showModal = false" max-width="lg">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <p class="text-[10px] font-black text-primary-dynamic uppercase tracking-[0.2em] mb-1">Financeiro</p>
                        <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tighter">
                            Novo Lançamento
                        </h2>
                    </div>
                    <button @click="showModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <div class="grid grid-cols-2 gap-8">
                        <div class="col-span-2">
                            <label class="block text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-3">Associado (Opcional)</label>
                            <select v-model="form.associado_id" class="w-full rounded-full border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-main)] px-5 py-4 focus:ring-primary-dynamic transition-all">
                                <option value="">Nenhum / Lançamento Geral</option>
                                <option v-for="a in associados" :key="a.id" :value="a.id">{{ a.nome }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-3">Tipo</label>
                            <div class="flex p-1.5 bg-slate-100 dark:bg-slate-800 rounded-full">
                                <button type="button" @click="form.tipo = 'receita'" :class="form.tipo === 'receita' ? 'bg-[var(--bg-card)] text-emerald-600 shadow-sm' : 'text-slate-400'" class="flex-1 py-3 text-[10px] font-black uppercase tracking-widest rounded-full transition-all">Receita</button>
                                <button type="button" @click="form.tipo = 'despesa'" :class="form.tipo === 'despesa' ? 'bg-[var(--bg-card)] text-rose-600 shadow-sm' : 'text-slate-400'" class="flex-1 py-3 text-[10px] font-black uppercase tracking-widest rounded-full transition-all">Despesa</button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-3">Valor (R$)</label>
                            <input v-model="form.valor" v-mask="'currency'" type="text" class="w-full rounded-full border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-main)] px-5 py-4 focus:ring-primary-dynamic transition-all" placeholder="R$ 0,00" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-3">Vencimento</label>
                            <input v-model="form.data_vencimento" type="date" class="w-full rounded-full border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-main)] px-5 py-4 focus:ring-primary-dynamic transition-all" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-3">Status</label>
                            <select v-model="form.status" class="w-full rounded-full border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-main)] px-5 py-4 focus:ring-primary-dynamic transition-all">
                                <option value="pendente">Pendente</option>
                                <option value="pago">Pago / Recebido</option>
                                <option value="atrasado">Atrasado</option>
                            </select>
                        </div>
                    </div>
                    <div class="pt-6 flex space-x-4">
                        <SecondaryButton type="button" @click="showModal = false" class="flex-1 !py-4 !text-[10px] !rounded-full">
                            Cancelar
                        </SecondaryButton>
                        <PrimaryButton type="submit" class="flex-1 !py-4 !text-[10px] !rounded-full shadow-xl shadow-primary-dynamic/20">
                            Confirmar Lançamento
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
