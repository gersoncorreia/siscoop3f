<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import {
    CheckCircleIcon,
    ClipboardDocumentIcon,
    ClipboardDocumentCheckIcon,
    QrCodeIcon,
    BuildingStorefrontIcon,
    CreditCardIcon,
    ArrowRightIcon,
    BanknotesIcon,
    UserIcon,
    CalendarIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    associado: Object,
    lancamento: Object,
    paymentConfig: Object,
});

const copied = ref(false);
const activeTab = ref('pix');

const copyPixKey = () => {
    if (!props.paymentConfig?.pix_manual?.chave_pix) return;
    navigator.clipboard.writeText(props.paymentConfig.pix_manual.chave_pix);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 3000);
};

const formatMoney = (val) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val || 0);
};
</script>

<template>
    <Head title="Adesão Concluída - Pagamento" />

    <GuestLayout maxWidthClass="sm:max-w-2xl">
        <div class="w-full space-y-6">
            <!-- Header Hero -->
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-14 h-14 bg-emerald-500/10 text-emerald-500 rounded-2xl shadow-sm mb-3">
                    <CheckCircleIcon class="w-8 h-8" />
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-[var(--text-heading)] tracking-tight">Cadastro Recebido com Sucesso!</h1>
                <p class="text-xs sm:text-sm text-[var(--text-muted)] mt-1 max-w-xl mx-auto">
                    Olá, <strong class="text-[var(--text-heading)]">{{ associado?.nome }}</strong>. Sua proposta de adesão foi registrada e está em fase de análise.
                </p>
            </div>

            <!-- Faixa de Resumo do Lançamento / Cobrança Inicial -->
            <div v-if="lancamento" class="p-4 sm:p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 border-l-4 border-l-emerald-500 shadow-sm">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-500/20">
                                Cobrança Inicial
                            </span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                {{ lancamento.status === 'pago' ? '🟢 Pago' : '🟡 Aguardando Pagamento' }}
                            </span>
                        </div>
                        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-1.5 pt-1">
                            <UserIcon class="w-4 h-4 text-slate-400" /> Titular: {{ associado?.nome }}
                        </h3>
                    </div>

                    <div class="flex items-center gap-6 sm:text-right">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Vencimento</p>
                            <p class="text-xs font-bold text-slate-600 dark:text-slate-300 flex items-center gap-1 mt-0.5">
                                <CalendarIcon class="w-3.5 h-3.5 text-slate-400" /> {{ lancamento.data_vencimento || 'Imediato' }}
                            </p>
                        </div>
                        <div class="pl-6 border-l border-slate-200 dark:border-slate-700">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Valor Total</p>
                            <p class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono mt-0.5">
                                {{ formatMoney(lancamento.valor) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Painel Principal de Opções de Pagamento -->
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
                <!-- Abas de Pagamento -->
                <div class="border-b border-slate-200 dark:border-slate-700 bg-slate-100/70 dark:bg-slate-800/50 p-2 flex flex-wrap gap-2">
                    <button 
                        @click="activeTab = 'pix'"
                        :class="[
                            'flex-1 min-w-[140px] py-2.5 px-4 rounded-xl text-xs font-black uppercase tracking-wider flex items-center justify-center gap-2 transition-all',
                            activeTab === 'pix' ? 'bg-white dark:bg-slate-800 text-emerald-600 shadow-sm border border-emerald-500/20' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'
                        ]"
                    >
                        <QrCodeIcon class="w-4 h-4" /> PIX Próprio
                    </button>
                    <button 
                        @click="activeTab = 'presencial'"
                        :class="[
                            'flex-1 min-w-[140px] py-2.5 px-4 rounded-xl text-xs font-black uppercase tracking-wider flex items-center justify-center gap-2 transition-all',
                            activeTab === 'presencial' ? 'bg-white dark:bg-slate-800 text-blue-600 shadow-sm border border-blue-500/20' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'
                        ]"
                    >
                        <BuildingStorefrontIcon class="w-4 h-4" /> Pagamento Presencial
                    </button>
                </div>

                <!-- Conteúdo Aba PIX (Sem cards cinzas extras e sem borda ao redor do QRCode) -->
                <div v-if="activeTab === 'pix'" class="p-5 sm:p-6 space-y-4">
                    <p v-if="paymentConfig?.pix_manual?.instrucoes" class="text-xs text-slate-500 text-center max-w-xl mx-auto leading-relaxed">
                        {{ paymentConfig.pix_manual.instrucoes }}
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-10 pt-2">
                        <!-- QRCode diretamente na tela (Sem caixa com borda) -->
                        <div v-if="paymentConfig?.pix_manual?.qrcode_image_url" class="shrink-0 text-center">
                            <img :src="paymentConfig.pix_manual.qrcode_image_url" alt="QRCode PIX" class="w-36 h-36 sm:w-40 sm:h-40 object-contain mx-auto" />
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1.5">Abra o app do seu banco</p>
                        </div>

                        <!-- Chave PIX em Texto Grande + Favorecido Completo Embaixo -->
                        <div class="w-full sm:w-auto space-y-3">
                            <div v-if="paymentConfig?.pix_manual?.chave_pix" class="space-y-2">
                                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                    Chave PIX ({{ paymentConfig.pix_manual.tipo_chave?.toUpperCase() }})
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xl sm:text-2xl font-black font-mono text-slate-900 dark:text-white tracking-tight select-all">
                                        {{ paymentConfig.pix_manual.chave_pix }}
                                    </span>
                                    <button 
                                        @click="copyPixKey" 
                                        class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold text-xs flex items-center gap-1.5 shrink-0 shadow transition-all"
                                    >
                                        <component :is="copied ? ClipboardDocumentCheckIcon : ClipboardDocumentIcon" class="w-4 h-4" />
                                        {{ copied ? 'Copiado!' : 'Copiar Chave' }}
                                    </button>
                                </div>

                                <!-- Favorecido por Extenso (Sem corte de texto) -->
                                <div v-if="paymentConfig.pix_manual.favorecido" class="text-xs text-slate-500 dark:text-slate-400 pt-1">
                                    <span class="font-bold text-slate-700 dark:text-slate-300">Favorecido:</span> {{ paymentConfig.pix_manual.favorecido }}
                                </div>
                            </div>
                            <div v-else class="text-xs text-slate-400 italic">
                                Chave PIX direta pendente de cadastro nas configurações da entidade.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conteúdo Aba Presencial -->
                <div v-if="activeTab === 'presencial'" class="p-6">
                    <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-2xl p-6 text-center max-w-xl mx-auto">
                        <CreditCardIcon class="w-10 h-10 text-blue-500 mx-auto mb-3" />
                        <h4 class="text-base font-black text-blue-900 dark:text-blue-300 uppercase tracking-tight">Pagamento em Dinheiro ou Maquininha</h4>
                        <p class="text-xs text-blue-800 dark:text-blue-400 mt-2 leading-relaxed">
                            {{ paymentConfig?.presencial?.instrucoes || 'Você também pode realizar o pagamento presencialmente na recepção da instituição em dinheiro ou máquina de cartão de crédito/débito.' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Botão Voltar ao Início (Ao Final) -->
            <div class="text-center pt-2">
                <Link 
                    :href="route('adesao.create')" 
                    class="px-8 py-3.5 bg-slate-900 hover:bg-slate-800 text-white dark:bg-slate-100 dark:text-slate-900 rounded-xl font-black text-xs uppercase tracking-widest inline-flex items-center justify-center gap-2 shadow-lg transition-all"
                >
                    Voltar ao Início
                    <ArrowRightIcon class="w-4 h-4" />
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>
