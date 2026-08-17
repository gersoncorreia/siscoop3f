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

    <GuestLayout>
        <div class="max-w-3xl mx-auto py-12 px-4 sm:px-6">
            <!-- Header Card -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-emerald-500/10 text-emerald-500 rounded-3xl shadow-xl shadow-emerald-500/10 mb-6">
                    <CheckCircleIcon class="w-10 h-10" />
                </div>
                <h1 class="text-3xl sm:text-4xl font-black text-[var(--text-heading)] tracking-tight">Cadastro Recebido com Sucesso!</h1>
                <p class="text-sm text-[var(--text-muted)] mt-2 max-w-lg mx-auto">
                    Olá, <strong class="text-[var(--text-heading)]">{{ associado?.nome }}</strong>. Sua proposta de adesão foi registrada e está em fase de análise documental.
                </p>
            </div>

            <!-- Resumo da Cobrança -->
            <div v-if="lancamento" class="zynix-card p-6 mb-8 border-l-4 border-l-emerald-500">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-500 bg-emerald-50 dark:bg-emerald-500/10 px-3 py-1 rounded-full border border-emerald-200 dark:border-emerald-500/20">
                            Taxa de Adesão & Cobrança Inicial
                        </span>
                        <h3 class="text-lg font-black text-[var(--text-heading)] mt-2">Valor da Cobrança Inicial</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Vencimento: {{ lancamento.data_vencimento || 'Adesão' }}</p>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
                            {{ formatMoney(lancamento.valor) }}
                        </span>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Status: {{ lancamento.status === 'pago' ? 'Pago' : 'Pendente' }}</p>
                    </div>
                </div>
            </div>

            <!-- Abas / Opções de Pagamento -->
            <div class="zynix-card overflow-hidden">
                <div class="border-b border-[var(--border-color)] bg-slate-50/50 dark:bg-slate-800/30 p-2 flex gap-2">
                    <button 
                        @click="activeTab = 'pix'"
                        :class="[
                            'flex-1 py-3 px-4 rounded-xl text-xs font-black uppercase tracking-wider flex items-center justify-center gap-2 transition-all',
                            activeTab === 'pix' ? 'bg-white dark:bg-slate-800 text-emerald-600 shadow-sm border border-emerald-500/20' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'
                        ]"
                    >
                        <QrCodeIcon class="w-4 h-4" /> Pagamento via PIX
                    </button>
                    <button 
                        @click="activeTab = 'presencial'"
                        :class="[
                            'flex-1 py-3 px-4 rounded-xl text-xs font-black uppercase tracking-wider flex items-center justify-center gap-2 transition-all',
                            activeTab === 'presencial' ? 'bg-white dark:bg-slate-800 text-blue-600 shadow-sm border border-blue-500/20' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'
                        ]"
                    >
                        <BuildingStorefrontIcon class="w-4 h-4" /> Pagamento Presencial
                    </button>
                </div>

                <!-- Conteúdo Aba PIX -->
                <div v-if="activeTab === 'pix'" class="p-6 sm:p-8 space-y-6">
                    <div class="text-center max-w-md mx-auto">
                        <p class="text-xs text-slate-500 leading-relaxed">
                            {{ paymentConfig?.pix_manual?.instrucoes || 'Efetue o pagamento via PIX apontando a câmera do celular para o QR Code abaixo ou copiando a chave.' }}
                        </p>
                    </div>

                    <!-- Exibição da Imagem do QRCode PIX -->
                    <div v-if="paymentConfig?.pix_manual?.qrcode_image_url" class="flex justify-center my-4">
                        <div class="p-4 bg-white rounded-2xl shadow-lg border border-slate-200 inline-block">
                            <img :src="paymentConfig.pix_manual.qrcode_image_url" alt="QRCode PIX" class="w-48 h-48 object-contain mx-auto" />
                            <p class="text-[10px] font-bold text-center text-slate-400 uppercase mt-2">Escaneie no app do seu banco</p>
                        </div>
                    </div>

                    <!-- Chave PIX Copia e Cola -->
                    <div v-if="paymentConfig?.pix_manual?.chave_pix" class="bg-slate-50 dark:bg-slate-800/50 border border-[var(--border-color)] rounded-2xl p-5 max-w-lg mx-auto">
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                Chave PIX ({{ paymentConfig.pix_manual.tipo_chave?.toUpperCase() }})
                            </span>
                            <span v-if="paymentConfig.pix_manual.favorecido" class="text-xs font-bold text-slate-600 dark:text-slate-300">
                                Favorecido: {{ paymentConfig.pix_manual.favorecido }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <input 
                                type="text" 
                                readonly 
                                :value="paymentConfig.pix_manual.chave_pix" 
                                class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl py-2.5 px-3 text-xs font-mono font-bold text-slate-800 dark:text-slate-200"
                            />
                            <button 
                                @click="copyPixKey" 
                                class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold text-xs flex items-center gap-1.5 shrink-0 shadow-lg shadow-emerald-500/20 transition-all"
                            >
                                <component :is="copied ? ClipboardDocumentCheckIcon : ClipboardDocumentIcon" class="w-4 h-4" />
                                {{ copied ? 'Copiado!' : 'Copiar' }}
                            </button>
                        </div>
                    </div>

                    <div v-else class="text-center py-4 text-xs text-slate-400 italic">
                        Chave PIX direta pendente de cadastro nas configurações da entidade.
                    </div>
                </div>

                <!-- Conteúdo Aba Presencial -->
                <div v-if="activeTab === 'presencial'" class="p-6 sm:p-8 space-y-6">
                    <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-2xl p-6 text-center max-w-lg mx-auto">
                        <CreditCardIcon class="w-10 h-10 text-blue-500 mx-auto mb-3" />
                        <h4 class="text-base font-black text-blue-900 dark:text-blue-300 uppercase tracking-tight">Pagamento em Dinheiro ou Máquina de Cartão</h4>
                        <p class="text-xs text-blue-800 dark:text-blue-400 mt-2 leading-relaxed">
                            {{ paymentConfig?.presencial?.instrucoes || 'Você também pode realizar o pagamento presencialmente na recepção da instituição em dinheiro ou máquina de cartão de crédito/débito.' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="mt-8 text-center flex justify-center gap-4">
                <Link 
                    :href="route('adesao.create')" 
                    class="px-8 py-3.5 bg-slate-900 hover:bg-slate-800 text-white dark:bg-slate-100 dark:text-slate-900 rounded-full font-black text-xs uppercase tracking-widest inline-flex items-center gap-2 shadow-lg transition-all"
                >
                    Voltar ao Início
                    <ArrowRightIcon class="w-4 h-4" />
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>
