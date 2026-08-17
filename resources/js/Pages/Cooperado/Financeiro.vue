<script setup>
import CooperadoLayout from '@/Layouts/CooperadoLayout.vue';
import { Head, usePage, router, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { 
    BanknotesIcon, 
    FunnelIcon, 
    QrCodeIcon, 
    CheckCircleIcon, 
    ClockIcon, 
    ClipboardDocumentIcon, 
    XMarkIcon,
    CurrencyDollarIcon
} from '@heroicons/vue/24/outline';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const page = usePage();
const lancamentos = computed(() => page.props.lancamentos);
const stats = computed(() => page.props.stats || {});
const paymentConfig = computed(() => page.props.paymentConfig || {});
const filtroStatus = computed(() => page.props.filtroStatus || '');

const statusFilter = ref(filtroStatus.value);
const selectedLancamento = ref(null);
const showPaymentModal = ref(false);
const copiedPix = ref(false);

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

function openPaymentModal(lanc) {
    selectedLancamento.value = lanc;
    showPaymentModal.value = true;
}

function copyPixKey() {
    const key = paymentConfig.value?.pix_manual?.chave_pix || '';
    if (key) {
        navigator.clipboard.writeText(key);
        copiedPix.value = true;
        setTimeout(() => copiedPix.value = false, 2000);
    }
}
</script>

<template>
    <Head title="Meu Financeiro" />
    <CooperadoLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">Meu Financeiro</h2>
                    <p class="text-sm text-[var(--text-muted)] mt-1 font-medium">Extrato de pagamentos, mensalidades e taxas de serviços da Santa Casa.</p>
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

        <div class="space-y-6 pb-12">
            <!-- Stats Overview Row -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="zynix-card">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Pago</span>
                        <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-500">
                            <CheckCircleIcon class="w-5 h-5" />
                        </div>
                    </div>
                    <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight mt-2">
                        {{ formatCurrency(stats.totalPago) }}
                    </h3>
                </div>

                <div class="zynix-card">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Pendente</span>
                        <div class="p-2 rounded-xl bg-amber-500/10 text-amber-500">
                            <ClockIcon class="w-5 h-5" />
                        </div>
                    </div>
                    <h3 class="text-2xl font-black text-amber-600 dark:text-amber-400 tracking-tight mt-2">
                        {{ formatCurrency(stats.totalPendente) }}
                    </h3>
                </div>

                <div class="zynix-card">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Cobranças Pendentes</span>
                        <div class="p-2 rounded-xl bg-primary-dynamic/10 text-primary-dynamic">
                            <BanknotesIcon class="w-5 h-5" />
                        </div>
                    </div>
                    <h3 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-2">
                        {{ stats.qtdPendentes || 0 }} pendência(s)
                    </h3>
                </div>
            </div>

            <!-- Table Card -->
            <div class="zynix-card !p-0 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="zynix-table w-full">
                        <thead>
                            <tr>
                                <th class="pl-6">Descrição / Serviço</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Vencimento</th>
                                <th>Pagamento / Registro</th>
                                <th class="text-right pr-6">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="lanc in lancamentos.data" :key="lanc.id" class="group">
                                <td class="pl-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="p-2 rounded-xl shrink-0"
                                            :class="lanc.status === 'pago' ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600' : 'bg-amber-50 dark:bg-amber-500/10 text-amber-600'"
                                        >
                                            <CurrencyDollarIcon class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <span class="text-xs font-black text-[var(--text-heading)] tracking-tight block">
                                                {{ lanc.categoria_label || 'Taxa / Serviço' }}
                                            </span>
                                            <span v-if="lanc.observacoes" class="text-[10px] text-slate-400 block truncate max-w-xs">
                                                {{ lanc.observacoes }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm font-black text-[var(--text-heading)] font-mono">
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
                                <td class="text-xs font-medium text-[var(--text-muted)] font-mono">{{ lanc.data_vencimento || '—' }}</td>
                                <td class="text-xs font-medium text-[var(--text-muted)] font-mono">{{ lanc.data_pagamento || lanc.created_at }}</td>
                                <td class="text-right pr-6 whitespace-nowrap">
                                    <button 
                                        v-if="lanc.status === 'pendente'"
                                        @click="openPaymentModal(lanc)" 
                                        class="px-3.5 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5 shadow-sm shadow-emerald-500/20 transition-all"
                                    >
                                        <QrCodeIcon class="w-4 h-4" />
                                        Pagar / PIX
                                    </button>
                                    <span v-else class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center justify-end gap-1">
                                        <CheckCircleIcon class="w-4 h-4" />
                                        Quitado
                                    </span>
                                </td>
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

            <!-- Modal Instruções de Pagamento / PIX -->
            <Modal :show="showPaymentModal" @close="showPaymentModal = false" max-width="md">
                <div v-if="selectedLancamento" class="p-6 sm:p-8 space-y-6">
                    <div class="flex items-center justify-between border-b border-[var(--border-color)] pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-bold">
                                <QrCodeIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em]">Pagamento PIX / Presencial</p>
                                <h2 class="text-lg font-black text-[var(--text-heading)] tracking-tighter">Instruções de Pagamento</h2>
                            </div>
                        </div>
                        <button @click="showPaymentModal = false" class="p-2 text-slate-400 hover:text-slate-600 transition-colors">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Detalhes da Cobrança -->
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl border border-[var(--border-color)] space-y-1">
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Cobrança Selecionada</span>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-sm font-black text-[var(--text-heading)]">{{ selectedLancamento.categoria_label }}</span>
                            <span class="text-base font-black text-emerald-600 dark:text-emerald-400 font-mono">{{ formatCurrency(selectedLancamento.valor) }}</span>
                        </div>
                        <p class="text-[10px] text-slate-400">Vencimento: {{ selectedLancamento.data_vencimento }}</p>
                    </div>

                    <!-- Dados do PIX da Santa Casa -->
                    <div class="p-4 bg-emerald-500/5 rounded-2xl border border-emerald-500/20 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400">PIX Próprio da Entidade</span>
                            <span class="text-[10px] font-bold text-slate-500 uppercase">Favorecido: {{ paymentConfig?.pix_manual?.favorecido || $page.props.tenant?.nome }}</span>
                        </div>

                        <!-- Chave PIX Copia e Cola -->
                        <div>
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 block mb-1">Chave PIX ({{ paymentConfig?.pix_manual?.tipo_chave?.toUpperCase() || 'CNPJ' }})</span>
                            <div class="flex items-center gap-2">
                                <input 
                                    type="text" 
                                    readonly 
                                    :value="paymentConfig?.pix_manual?.chave_pix || 'Consulte na Recepção'" 
                                    class="w-full bg-white dark:bg-slate-900 border border-[var(--border-color)] rounded-xl py-2 px-3 text-xs font-mono font-bold text-[var(--text-heading)]" 
                                />
                                <button 
                                    @click="copyPixKey" 
                                    class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold transition-colors shrink-0 inline-flex items-center gap-1.5"
                                >
                                    <ClipboardDocumentIcon class="w-4 h-4" />
                                    {{ copiedPix ? 'Copiado!' : 'Copiar' }}
                                </button>
                            </div>
                        </div>

                        <!-- QR Code Image se houver -->
                        <div v-if="paymentConfig?.pix_manual?.qrcode_image_url" class="text-center pt-2">
                            <img :src="paymentConfig.pix_manual.qrcode_image_url" alt="QR Code PIX" class="w-36 h-36 mx-auto rounded-xl border border-slate-200 shadow-sm" />
                            <p class="text-[10px] text-slate-400 mt-2">Aponta a câmera do aplicativo do seu banco para o QR Code acima</p>
                        </div>
                    </div>

                    <!-- Instruções Presenciais -->
                    <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-[var(--border-color)] space-y-1">
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pagamento Presencial na Recepção</span>
                        <p class="text-xs text-[var(--text-muted)] leading-relaxed mt-1">
                            Você também pode efetuar o pagamento diretamente na recepção em dinheiro ou cartão de débito/crédito.
                        </p>
                    </div>

                    <div class="pt-4 flex justify-end border-t border-[var(--border-color)]">
                        <SecondaryButton @click="showPaymentModal = false" type="button" class="!rounded-full !py-2.5 !px-6">
                            Fechar
                        </SecondaryButton>
                    </div>
                </div>
            </Modal>
        </div>
    </CooperadoLayout>
</template>
