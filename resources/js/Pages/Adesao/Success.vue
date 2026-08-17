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
        <div class="max-w-4xl mx-auto py-4 sm:py-6 px-4">
            <!-- Compact Header -->
            <div class="text-center mb-5">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-emerald-500/10 text-emerald-500 rounded-2xl shadow-md mb-2">
                    <CheckCircleIcon class="w-7 h-7" />
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-[var(--text-heading)] tracking-tight">Cadastro Recebido com Sucesso!</h1>
                <p class="text-xs text-[var(--text-muted)] mt-1 max-w-md mx-auto">
                    Olá, <strong class="text-[var(--text-heading)]">{{ associado?.nome }}</strong>. Sua solicitação foi registrada com sucesso.
                </p>
            </div>

            <!-- Main Split Grid (2 Colunas em telas médias/grandes) -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-start">
                
                <!-- Coluna 1: Resumo da Cobrança (5 colunas) -->
                <div class="md:col-span-5 space-y-4">
                    <div v-if="lancamento" class="zynix-card p-5 border-l-4 border-l-emerald-500 shadow-md">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-500/20">
                                Cobrança Inicial
                            </span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                {{ lancamento.status === 'pago' ? '🟢 Pago' : '🟡 Pendente' }}
                            </span>
                        </div>

                        <div class="mb-4">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Valor da Adesão</p>
                            <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono mt-0.5">
                                {{ formatMoney(lancamento.valor) }}
                            </p>
                            <p class="text-[11px] text-slate-500 mt-1">Vencimento: {{ lancamento.data_vencimento || 'Imediato' }}</p>
                        </div>

                        <div class="pt-3 border-t border-[var(--border-color)] text-xs space-y-1.5">
                            <div class="flex items-center justify-between text-slate-600 dark:text-slate-300">
                                <span class="font-bold flex items-center gap-1.5"><UserIcon class="w-3.5 h-3.5 text-slate-400" /> Titular:</span>
                                <span class="font-medium truncate max-w-[150px]">{{ associado?.nome }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-600 dark:text-slate-300">
                                <span class="font-bold flex items-center gap-1.5"><BanknotesIcon class="w-3.5 h-3.5 text-slate-400" /> Destino:</span>
                                <span class="font-medium">Taxa de Adesão</span>
                            </div>
                        </div>
                    </div>

                    <!-- Botão de Ação -->
                    <div class="text-center">
                        <Link 
                            :href="route('adesao.create')" 
                            class="w-full py-3 bg-slate-900 hover:bg-slate-800 text-white dark:bg-slate-100 dark:text-slate-900 rounded-xl font-black text-xs uppercase tracking-widest inline-flex items-center justify-center gap-2 shadow-md transition-all"
                        >
                            Voltar ao Início
                            <ArrowRightIcon class="w-4 h-4" />
                        </Link>
                    </div>
                </div>

                <!-- Coluna 2: Painel de Pagamentos (7 colunas) -->
                <div class="md:col-span-7">
                    <div class="zynix-card overflow-hidden shadow-md">
                        <!-- Abas Compactas -->
                        <div class="border-b border-[var(--border-color)] bg-slate-50/50 dark:bg-slate-800/30 p-1.5 flex gap-1.5">
                            <button 
                                @click="activeTab = 'pix'"
                                :class="[
                                    'flex-1 py-2 px-3 rounded-lg text-xs font-black uppercase tracking-wider flex items-center justify-center gap-1.5 transition-all',
                                    activeTab === 'pix' ? 'bg-white dark:bg-slate-800 text-emerald-600 shadow-sm border border-emerald-500/20' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'
                                ]"
                            >
                                <QrCodeIcon class="w-4 h-4" /> PIX Próprio
                            </button>
                            <button 
                                @click="activeTab = 'presencial'"
                                :class="[
                                    'flex-1 py-2 px-3 rounded-lg text-xs font-black uppercase tracking-wider flex items-center justify-center gap-1.5 transition-all',
                                    activeTab === 'presencial' ? 'bg-white dark:bg-slate-800 text-blue-600 shadow-sm border border-blue-500/20' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'
                                ]"
                            >
                                <BuildingStorefrontIcon class="w-4 h-4" /> Presencial
                            </button>
                        </div>

                        <!-- Conteúdo Aba PIX -->
                        <div v-if="activeTab === 'pix'" class="p-4 sm:p-5 space-y-4">
                            <p class="text-xs text-slate-500 text-center leading-tight">
                                {{ paymentConfig?.pix_manual?.instrucoes || 'Efetue o pagamento via PIX apontando a câmera do celular para o QR Code ou copiando a chave.' }}
                            </p>

                            <!-- Layout Lado a Lado no PIX (QRCode + Chave) -->
                            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 bg-slate-50 dark:bg-slate-800/40 border border-[var(--border-color)] rounded-xl p-3.5">
                                <!-- Imagem do QRCode PIX -->
                                <div v-if="paymentConfig?.pix_manual?.qrcode_image_url" class="shrink-0">
                                    <div class="p-2 bg-white rounded-xl shadow border border-slate-200 inline-block">
                                        <img :src="paymentConfig.pix_manual.qrcode_image_url" alt="QRCode PIX" class="w-32 h-32 object-contain mx-auto" />
                                    </div>
                                </div>

                                <!-- Copia e Cola -->
                                <div class="w-full space-y-2">
                                    <div v-if="paymentConfig?.pix_manual?.chave_pix">
                                        <div class="flex items-center justify-between text-[10px] font-bold text-slate-400 uppercase mb-1">
                                            <span>Chave ({{ paymentConfig.pix_manual.tipo_chave?.toUpperCase() }})</span>
                                            <span v-if="paymentConfig.pix_manual.favorecido" class="truncate max-w-[120px]">{{ paymentConfig.pix_manual.favorecido }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <input 
                                                type="text" 
                                                readonly 
                                                :value="paymentConfig.pix_manual.chave_pix" 
                                                class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-2.5 text-xs font-mono font-bold text-slate-800 dark:text-slate-200"
                                            />
                                            <button 
                                                @click="copyPixKey" 
                                                class="px-3.5 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg font-bold text-xs flex items-center gap-1 shrink-0 shadow transition-all"
                                            >
                                                <component :is="copied ? ClipboardDocumentCheckIcon : ClipboardDocumentIcon" class="w-3.5 h-3.5" />
                                                {{ copied ? 'Copiado!' : 'Copiar' }}
                                            </button>
                                        </div>
                                    </div>
                                    <div v-else class="text-center py-2 text-xs text-slate-400 italic">
                                        Chave PIX pendente de cadastro.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Conteúdo Aba Presencial -->
                        <div v-if="activeTab === 'presencial'" class="p-4 sm:p-5">
                            <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-xl p-4 text-center">
                                <CreditCardIcon class="w-8 h-8 text-blue-500 mx-auto mb-2" />
                                <h4 class="text-sm font-black text-blue-900 dark:text-blue-300 uppercase tracking-tight">Pagamento em Dinheiro ou Maquininha</h4>
                                <p class="text-xs text-blue-800 dark:text-blue-400 mt-1.5 leading-relaxed">
                                    {{ paymentConfig?.presencial?.instrucoes || 'Você também pode realizar o pagamento presencialmente na recepção da instituição em dinheiro ou máquina de cartão de crédito/débito.' }}
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </GuestLayout>
</template>
