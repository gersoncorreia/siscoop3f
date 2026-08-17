<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { ref, computed } from 'vue';
import {
    CreditCardIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    XMarkIcon,
    Cog6ToothIcon,
    QrCodeIcon,
    BuildingStorefrontIcon,
    BoltIcon,
    CheckBadgeIcon,
    PhotoIcon,
    PlusIcon,
    TrashIcon,
    CurrencyDollarIcon,
    UserPlusIcon,
    BuildingOffice2Icon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    paymentConfig: Object,
});

const page = usePage();
const baseUrl = computed(() => window.location.origin);
const isCooperativa = computed(() => page.props.tenant?.tipo !== 'fundacao');

const form = useForm({
    active_gateway: props.paymentConfig.active_gateway || 'interno',
    pix_manual: {
        enabled: Boolean(props.paymentConfig.pix_manual?.enabled ?? true),
        tipo_chave: props.paymentConfig.pix_manual?.tipo_chave || 'cnpj',
        chave_pix: props.paymentConfig.pix_manual?.chave_pix || '',
        favorecido: props.paymentConfig.pix_manual?.favorecido || '',
        instrucoes: props.paymentConfig.pix_manual?.instrucoes || '',
        qrcode_image: null,
    },
    presencial: {
        enabled: Boolean(props.paymentConfig.presencial?.enabled ?? true),
        aceita_dinheiro: Boolean(props.paymentConfig.presencial?.aceita_dinheiro ?? true),
        aceita_maquininha: Boolean(props.paymentConfig.presencial?.aceita_maquininha ?? true),
        instrucoes: props.paymentConfig.presencial?.instrucoes || '',
    },
    asaas: {
        enabled: Boolean(props.paymentConfig.asaas?.enabled ?? false),
        api_key: props.paymentConfig.asaas?.api_key || '',
        environment: props.paymentConfig.asaas?.environment || 'sandbox',
        webhook_token: props.paymentConfig.asaas?.webhook_token || '',
    },
    taxas_adesao: Array.isArray(props.paymentConfig.taxas_adesao) ? props.paymentConfig.taxas_adesao.map((t, idx) => ({
        id: t.id || String(idx + 1),
        titulo: t.titulo || '',
        valor: parseFloat(t.valor) || 0,
        ativo: Boolean(t.ativo ?? true),
    })) : [
        { id: '1', titulo: 'Taxa Administrativa (Titular)', valor: 100, ativo: true },
        { id: '2', titulo: 'Taxa de Uso do Sistema', valor: 10, ativo: true }
    ],
    taxa_dependente_legal: parseFloat(props.paymentConfig.taxa_dependente_legal ?? 80),
    taxa_dependente_nao_legal: parseFloat(props.paymentConfig.taxa_dependente_nao_legal ?? 100),
    cota_capital_config: {
        meta_total: parseFloat(props.paymentConfig.cota_capital_config?.meta_total ?? 600),
        valor_quota: parseFloat(props.paymentConfig.cota_capital_config?.valor_quota ?? 10),
        qtd_quotas: parseInt(props.paymentConfig.cota_capital_config?.qtd_quotas ?? 60),
        valor_mensal: parseFloat(props.paymentConfig.cota_capital_config?.valor_mensal ?? 10),
        aporte_inicial: parseFloat(props.paymentConfig.cota_capital_config?.aporte_inicial ?? 10),
    }
});

const addTaxaAdesao = () => {
    form.taxas_adesao.push({
        id: String(Date.now()),
        titulo: 'Nova Cobrança',
        valor: 0,
        ativo: true
    });
};

const removeTaxaAdesao = (index) => {
    form.taxas_adesao.splice(index, 1);
};

const activeModal = ref(null);
const qrcodePreview = ref(props.paymentConfig.pix_manual?.qrcode_image_url || null);

const handleQrCodeUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.pix_manual.qrcode_image = file;
        qrcodePreview.value = URL.createObjectURL(file);
    }
};

const gateways = computed(() => [
    {
        id: 'pix_manual',
        name: 'PIX Manual (Chave & QR Code Próprio)',
        desc: 'Exibe a Chave PIX e a imagem do QRCode da entidade para o cliente pagar diretamente.',
        icon: QrCodeIcon,
        color: 'emerald',
        active: form.pix_manual.enabled,
        configured: !!form.pix_manual.chave_pix,
    },
    {
        id: 'presencial',
        name: 'Pagamento Presencial (Dinheiro & Maquininha)',
        desc: 'Permite recebimentos físicos em espécie e máquinas de cartão na recepção.',
        icon: BuildingStorefrontIcon,
        color: 'blue',
        active: form.presencial.enabled,
        configured: true,
    },
    {
        id: 'asaas',
        name: 'Integração Asaas API',
        desc: 'Emissão automatizada de Boletos e PIX via API com retorno por Webhook.',
        icon: BoltIcon,
        color: 'indigo',
        active: form.active_gateway === 'asaas',
        configured: !!form.asaas.api_key,
    },
]);

const openModal = (id) => {
    activeModal.value = id;
};

const closeModal = () => {
    activeModal.value = null;
};

const submitForm = () => {
    form.post(route('configuracoes.pagamentos.update'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            activeModal.value = null;
            if (props.paymentConfig.pix_manual?.qrcode_image_url) {
                qrcodePreview.value = props.paymentConfig.pix_manual.qrcode_image_url;
            }
        }
    });
};
</script>

<template>
    <Head title="Ajustes de Pagamentos" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <Breadcrumbs />
                <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Opções de Cobrança e Pagamentos</h2>
                <p class="text-sm text-[var(--text-muted)] mt-0.5">Configure o PIX Próprio, Pagamento Presencial e Gateways Automáticos da Entidade.</p>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Flash -->
            <div v-if="$page.props.flash?.success" class="p-4 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20 rounded-2xl font-bold flex items-center gap-3">
                <CheckBadgeIcon class="w-5 h-5" />
                {{ $page.props.flash.success }}
            </div>

            <!-- Card: Gerenciamento dos Valores do Resumo Financeiro da Adesão -->
            <div class="zynix-card overflow-hidden">
                <div class="p-6 border-b border-[var(--border-color)] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary-dynamic/10 text-primary-dynamic flex items-center justify-center font-bold">
                            <CurrencyDollarIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">Valores e Cobranças da Adesão Digital</h3>
                            <p class="text-xs text-[var(--text-muted)] mt-0.5">Gerencie os itens e valores exibidos no Resumo Financeiro do formulário público de adesão.</p>
                        </div>
                    </div>

                    <PrimaryButton type="button" @click="submitForm" :disabled="form.processing" class="!py-2.5 !px-5 !rounded-xl !text-xs !font-black uppercase tracking-widest">
                        Salvar Cobranças
                    </PrimaryButton>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Tabela de Taxas Customizáveis -->
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-black uppercase tracking-widest text-[var(--text-heading)]">Taxas e Cobranças Fixas do Titular</span>
                            <button type="button" @click="addTaxaAdesao" class="inline-flex items-center gap-1.5 text-xs font-bold text-primary-dynamic hover:underline">
                                <PlusIcon class="w-4 h-4" />
                                Adicionar Nova Cobrança
                            </button>
                        </div>

                        <div class="space-y-3">
                            <div v-for="(taxa, index) in form.taxas_adesao" :key="taxa.id" class="flex flex-col sm:flex-row items-center gap-4 p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-[var(--border-color)]">
                                <div class="flex-1 w-full">
                                    <InputLabel value="Descrição da Cobrança" class="!text-[9px] !font-black uppercase tracking-widest mb-1" />
                                    <TextInput v-model="taxa.titulo" type="text" class="w-full !py-2 text-xs" placeholder="Ex: Taxa Administrativa, Taxa de Inscrição..." required />
                                </div>
                                <div class="w-full sm:w-40">
                                    <InputLabel value="Valor (R$)" class="!text-[9px] !font-black uppercase tracking-widest mb-1" />
                                    <TextInput v-model="taxa.valor" type="number" step="0.01" min="0" class="w-full !py-2 text-xs font-mono font-bold" required />
                                </div>
                                <div class="flex items-center gap-4 pt-3 sm:pt-4">
                                    <label class="flex items-center gap-2 text-xs font-bold text-[var(--text-heading)] cursor-pointer">
                                        <input type="checkbox" v-model="taxa.ativo" class="w-4 h-4 rounded text-primary-dynamic focus:ring-primary-dynamic" />
                                        Ativo
                                    </label>
                                    <button type="button" @click="removeTaxaAdesao(index)" class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-lg transition-colors">
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            <div v-if="form.taxas_adesao.length === 0" class="p-6 text-center text-xs text-[var(--text-muted)] italic bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-dashed border-[var(--border-color)]">
                                Nenhuma taxa cadastrada. Clique em "Adicionar Nova Cobrança" acima.
                            </div>
                        </div>
                    </div>

                    <!-- Taxas por Dependente -->
                    <div class="pt-4 border-t border-[var(--border-color)]/50">
                        <span class="block text-xs font-black uppercase tracking-widest text-[var(--text-heading)] mb-4">Taxas de Adesão por Dependente</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-[var(--border-color)] space-y-1">
                                <InputLabel value="Taxa por Dependente Legal (R$)" class="!text-[10px] !font-black uppercase tracking-widest" />
                                <TextInput v-model="form.taxa_dependente_legal" type="number" step="0.01" min="0" class="w-full !py-2 text-xs font-mono font-bold" />
                                <p class="text-[10px] text-slate-400">Valor cobrado na adesão por cada dependente legal adicionado.</p>
                            </div>
                            <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-[var(--border-color)] space-y-1">
                                <InputLabel value="Taxa por Dependente Não-Legal (R$)" class="!text-[10px] !font-black uppercase tracking-widest" />
                                <TextInput v-model="form.taxa_dependente_nao_legal" type="number" step="0.01" min="0" class="w-full !py-2 text-xs font-mono font-bold" />
                                <p class="text-[10px] text-slate-400">Valor cobrado na adesão por dependentes não-legais (agregados).</p>
                            </div>
                        </div>
                    </div>

                    <!-- Cota Capital (Apenas Cooperativas, Omitido para Fundação) -->
                    <div v-if="isCooperativa" class="pt-4 border-t border-[var(--border-color)]/50">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="text-xs font-black uppercase tracking-widest text-[var(--text-heading)]">Configuração de Cota Capital (Apenas Cooperativas)</span>
                            <span class="text-[10px] font-bold text-amber-600 bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 rounded border border-amber-200 dark:border-amber-500/20 uppercase">Cooperativa</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-[var(--border-color)] space-y-1">
                                <InputLabel value="Aporte Inicial na Adesão (R$)" class="!text-[10px] !font-black uppercase tracking-widest" />
                                <TextInput v-model="form.cota_capital_config.aporte_inicial" type="number" step="0.01" min="0" class="w-full !py-2 text-xs font-mono font-bold" />
                                <p class="text-[10px] text-slate-400">Valor da 1ª cota cobrado hoje na adesão.</p>
                            </div>
                            <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-[var(--border-color)] space-y-1">
                                <InputLabel value="Meta Total da Cota (R$)" class="!text-[10px] !font-black uppercase tracking-widest" />
                                <TextInput v-model="form.cota_capital_config.meta_total" type="number" step="0.01" min="0" class="w-full !py-2 text-xs font-mono font-bold" />
                                <p class="text-[10px] text-slate-400">Meta total acumulada da cota capital do cooperado.</p>
                            </div>
                            <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-[var(--border-color)] space-y-1">
                                <InputLabel value="Valor Mensal da Quota (R$)" class="!text-[10px] !font-black uppercase tracking-widest" />
                                <TextInput v-model="form.cota_capital_config.valor_quota" type="number" step="0.01" min="0" class="w-full !py-2 text-xs font-mono font-bold" />
                                <p class="text-[10px] text-slate-400">Valor de cada quota mensal adicional.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grid de Opções de Pagamento -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="gw in gateways" :key="gw.id" class="zynix-card relative overflow-hidden group hover:border-primary-dynamic/30 transition-all">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div :class="[`w-12 h-12 rounded-2xl flex items-center justify-center bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300`]">
                                <component :is="gw.icon" class="w-6 h-6" />
                            </div>
                            <div>
                                <span v-if="gw.active" class="inline-flex items-center gap-1 text-[10px] bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-black px-3 py-1 rounded-full uppercase tracking-widest border border-emerald-200 dark:border-emerald-500/20">
                                    <CheckCircleIcon class="w-3 h-3" /> Habilitado
                                </span>
                                <span v-else class="inline-flex items-center gap-1 text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-500 font-black px-3 py-1 rounded-full uppercase tracking-widest border border-slate-200 dark:border-slate-700">
                                    Desabilitado
                                </span>
                            </div>
                        </div>
                        <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">{{ gw.name }}</h3>
                        <p class="text-xs text-slate-500 mt-2 font-medium leading-relaxed">{{ gw.desc }}</p>

                        <div class="mt-6 flex items-center justify-end">
                            <PrimaryButton @click="openModal(gw.id)" class="!px-6 !py-2 !rounded-full !text-[10px] !font-black !uppercase !tracking-widest">
                                Configurar
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal: PIX Manual -->
            <Modal :show="activeModal === 'pix_manual'" @close="closeModal" max-width="lg">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <p class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em] mb-1">PIX Próprio</p>
                            <h2 class="text-xl font-black text-[var(--text-heading)] tracking-tighter">Configurar PIX Manual</h2>
                        </div>
                        <button @click="closeModal" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitForm" class="space-y-5">
                        <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-[var(--border-color)]">
                            <span class="text-xs font-bold text-[var(--text-heading)]">Habilitar PIX Manual na tela de adesão</span>
                            <input type="checkbox" v-model="form.pix_manual.enabled" class="rounded text-emerald-500 focus:ring-emerald-500 w-5 h-5" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <InputLabel value="Tipo de Chave *" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                                <select v-model="form.pix_manual.tipo_chave" class="mt-1.5 w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] py-2.5 px-3 text-xs font-medium">
                                    <option value="cnpj">CNPJ</option>
                                    <option value="cpf">CPF</option>
                                    <option value="email">E-mail</option>
                                    <option value="telefone">Telefone / Celular</option>
                                    <option value="evp">Chave Aleatória (EVP)</option>
                                </select>
                            </div>

                            <div>
                                <InputLabel value="Valor da Chave PIX *" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                                <TextInput v-model="form.pix_manual.chave_pix" type="text" class="mt-1.5 block w-full text-xs font-mono" placeholder="Ex: 00.000.000/0001-00" />
                            </div>
                        </div>

                        <div>
                            <InputLabel value="Favorecido / Razão Social *" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                            <TextInput v-model="form.pix_manual.favorecido" type="text" class="mt-1.5 block w-full text-xs" placeholder="Ex: Fundação Santa Casa da Amazônia" />
                        </div>

                        <div>
                            <InputLabel value="Imagem do QR Code PIX (Opcional)" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                            <div class="mt-2 flex items-center gap-4">
                                <div v-if="qrcodePreview" class="w-24 h-24 rounded-xl border border-slate-200 p-1 bg-white flex items-center justify-center shrink-0">
                                    <img :src="qrcodePreview" alt="Preview QR Code" class="w-full h-full object-contain" />
                                </div>
                                <label class="cursor-pointer px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-xl text-xs font-bold text-[var(--text-heading)] inline-flex items-center gap-2 border border-slate-300 dark:border-slate-700">
                                    <PhotoIcon class="w-4 h-4 text-emerald-500" />
                                    Selecionar Imagem QR Code
                                    <input type="file" @change="handleQrCodeUpload" accept="image/*" class="hidden" />
                                </label>
                            </div>
                        </div>

                        <div>
                            <InputLabel value="Instruções Adicionais para o Cliente" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                            <textarea v-model="form.pix_manual.instrucoes" rows="2" class="mt-1.5 w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] text-xs p-3" placeholder="Instruções para exibição na tela de confirmação..."></textarea>
                        </div>

                        <div class="pt-4 flex justify-end gap-3 border-t border-[var(--border-color)]">
                            <SecondaryButton @click="closeModal" type="button" class="!rounded-full !py-3 !px-6">Cancelar</SecondaryButton>
                            <PrimaryButton type="submit" :disabled="form.processing" class="!rounded-full !py-3 !px-6 !bg-emerald-500 hover:!bg-emerald-600 shadow-lg shadow-emerald-500/20">
                                Salvar Alterações
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>

            <!-- Modal: Presencial -->
            <Modal :show="activeModal === 'presencial'" @close="closeModal" max-width="lg">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <p class="text-[10px] font-black text-blue-500 uppercase tracking-[0.2em] mb-1">Presencial</p>
                            <h2 class="text-xl font-black text-[var(--text-heading)] tracking-tighter">Configurar Recebimento Presencial</h2>
                        </div>
                        <button @click="closeModal" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitForm" class="space-y-5">
                        <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-[var(--border-color)]">
                            <span class="text-xs font-bold text-[var(--text-heading)]">Habilitar opção de Pagamento Presencial</span>
                            <input type="checkbox" v-model="form.presencial.enabled" class="rounded text-blue-500 focus:ring-blue-500 w-5 h-5" />
                        </div>

                        <div class="space-y-3">
                            <label class="flex items-center gap-3">
                                <input type="checkbox" v-model="form.presencial.aceita_dinheiro" class="rounded text-blue-500 w-4 h-4" />
                                <span class="text-xs font-bold text-[var(--text-heading)]">Aceitar pagamento em Dinheiro em espécie na recepção</span>
                            </label>
                            <label class="flex items-center gap-3">
                                <input type="checkbox" v-model="form.presencial.aceita_maquininha" class="rounded text-blue-500 w-4 h-4" />
                                <span class="text-xs font-bold text-[var(--text-heading)]">Aceitar Máquina de Cartão de Crédito/Débito física</span>
                            </label>
                        </div>

                        <div>
                            <InputLabel value="Instruções para o Cliente" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                            <textarea v-model="form.presencial.instrucoes" rows="2" class="mt-1.5 w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] text-xs p-3"></textarea>
                        </div>

                        <div class="pt-4 flex justify-end gap-3 border-t border-[var(--border-color)]">
                            <SecondaryButton @click="closeModal" type="button" class="!rounded-full !py-3 !px-6">Cancelar</SecondaryButton>
                            <PrimaryButton type="submit" :disabled="form.processing" class="!rounded-full !py-3 !px-6 !bg-blue-500 hover:!bg-blue-600 shadow-lg shadow-blue-500/20">
                                Salvar Alterações
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>

            <!-- Modal: Asaas -->
            <Modal :show="activeModal === 'asaas'" @close="closeModal" max-width="lg">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <p class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em] mb-1">Gateway API</p>
                            <h2 class="text-xl font-black text-[var(--text-heading)] tracking-tighter">Configurar Asaas API</h2>
                        </div>
                        <button @click="closeModal" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitForm" class="space-y-5">
                        <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-[var(--border-color)]">
                            <span class="text-xs font-bold text-[var(--text-heading)]">Ativar Asaas como Gateway de Produção</span>
                            <input type="checkbox" v-model="form.asaas.enabled" class="rounded text-indigo-500 focus:ring-indigo-500 w-5 h-5" />
                        </div>

                        <div>
                            <InputLabel value="Chave de API (API Key) *" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                            <TextInput v-model="form.asaas.api_key" type="password" class="mt-1.5 block w-full font-mono" placeholder="$aact_YTU5YTE0M2M2MWM..." />
                            <InputError :message="form.errors['asaas.api_key']" class="mt-1" />
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <InputLabel value="Ambiente *" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                                <select v-model="form.asaas.environment" class="mt-1.5 w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] py-2.5 px-3 text-xs font-medium">
                                    <option value="sandbox">🧪 Sandbox (Testes)</option>
                                    <option value="production">🚀 Produção</option>
                                </select>
                            </div>
                            <div>
                                <InputLabel value="Token do Webhook (Opcional)" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                                <TextInput v-model="form.asaas.webhook_token" type="password" class="mt-1.5 block w-full font-mono" placeholder="Token secreto..." />
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end gap-3 border-t border-[var(--border-color)]">
                            <SecondaryButton @click="closeModal" type="button" class="!rounded-full !py-3 !px-6">Cancelar</SecondaryButton>
                            <PrimaryButton type="submit" :disabled="form.processing" class="!rounded-full !py-3 !px-6 !bg-indigo-500 hover:!bg-indigo-600 shadow-lg shadow-indigo-500/20">
                                Salvar Alterações
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>

        </div>
    </AuthenticatedLayout>
</template>
