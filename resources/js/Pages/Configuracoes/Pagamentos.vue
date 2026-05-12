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
    ShieldCheckIcon,
    BoltIcon,
    GlobeAltIcon,
    KeyIcon,
    CheckBadgeIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    paymentConfig: Object,
});

const page = usePage();
const baseUrl = computed(() => window.location.origin);

const form = useForm({
    active_gateway: props.paymentConfig.active_gateway || 'interno',
    asaas: {
        api_key: props.paymentConfig.asaas?.api_key || '',
        environment: props.paymentConfig.asaas?.environment || 'sandbox',
        webhook_token: props.paymentConfig.asaas?.webhook_token || '',
    },
    stripe: {
        public_key: props.paymentConfig.stripe?.public_key || '',
        secret_key: props.paymentConfig.stripe?.secret_key || '',
        webhook_secret: props.paymentConfig.stripe?.webhook_secret || '',
    },
    mercadopago: {
        access_token: props.paymentConfig.mercadopago?.access_token || '',
        public_key: props.paymentConfig.mercadopago?.public_key || '',
    },
});

const activeModal = ref(null);

const gateways = computed(() => [
    {
        id: 'interno',
        name: 'Cobrança Interna (Sistema)',
        desc: 'Apenas geração de títulos e controle manual no sistema.',
        icon: CreditCardIcon,
        color: 'blue',
        active: form.active_gateway === 'interno',
    },
    {
        id: 'asaas',
        name: 'Integração Asaas',
        desc: 'Emissão automatizada de Boletos e PIX com retorno instantâneo.',
        icon: BoltIcon,
        color: 'emerald',
        active: form.active_gateway === 'asaas',
        configured: !!form.asaas.api_key,
    },
    {
        id: 'stripe',
        name: 'Integração Stripe',
        desc: 'Cobrança via Cartão de Crédito recorrente.',
        icon: GlobeAltIcon,
        color: 'violet',
        active: form.active_gateway === 'stripe',
        configured: !!form.stripe.secret_key,
    },
    {
        id: 'mercadopago',
        name: 'Mercado Pago',
        desc: 'Geração de PIX e split de pagamentos.',
        icon: ShieldCheckIcon,
        color: 'sky',
        active: form.active_gateway === 'mercadopago',
        configured: !!form.mercadopago.access_token,
    },
]);

const openModal = (id) => {
    activeModal.value = id;
};

const closeModal = () => {
    activeModal.value = null;
};

const activateGateway = (id) => {
    form.active_gateway = id;
    submitForm();
};

const submitForm = () => {
    form.post(route('configuracoes.pagamentos.update'), {
        preserveScroll: true,
        onSuccess: () => {
            activeModal.value = null;
        }
    });
};

const maskKey = (key) => {
    if (!key || key.length < 8) return '';
    return key.substring(0, 4) + '••••••••' + key.substring(key.length - 4);
};
</script>

<template>
    <Head title="Gateways de Pagamento" />

    <AuthenticatedLayout>
        <div class="py-6 sm:py-8 lg:py-12">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <Breadcrumbs />
                        <h1 class="text-2xl lg:text-3xl font-black text-[var(--text-heading)] tracking-tighter mt-2">
                            Ajustes de Pagamentos
                        </h1>
                    </div>
                </div>

                <!-- Flash -->
                <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20 rounded-2xl font-bold flex items-center gap-3">
                    <CheckBadgeIcon class="w-5 h-5" />
                    {{ $page.props.flash.success }}
                </div>

                <!-- Grid de Gateways -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div v-for="gw in gateways" :key="gw.id" class="zynix-card relative overflow-hidden group hover:border-primary-dynamic/30 transition-all">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div :class="[`w-12 h-12 rounded-2xl flex items-center justify-center`, gw.active ? `bg-${gw.color}-500/10 text-${gw.color}-500` : 'bg-slate-100 dark:bg-slate-800 text-slate-400']">
                                    <component :is="gw.icon" class="w-6 h-6" />
                                </div>
                                <div>
                                    <span v-if="gw.active" class="inline-flex items-center gap-1 text-[10px] bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-black px-3 py-1 rounded-full uppercase tracking-widest border border-emerald-200 dark:border-emerald-500/20">
                                        <CheckCircleIcon class="w-3 h-3" /> Ativo
                                    </span>
                                    <span v-else-if="gw.configured" class="inline-flex items-center gap-1 text-[10px] bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 font-black px-3 py-1 rounded-full uppercase tracking-widest border border-amber-200 dark:border-amber-500/20">
                                        <Cog6ToothIcon class="w-3 h-3" /> Configurado
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-500 font-black px-3 py-1 rounded-full uppercase tracking-widest border border-slate-200 dark:border-slate-700">
                                        Desabilitado
                                    </span>
                                </div>
                            </div>
                            <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">{{ gw.name }}</h3>
                            <p class="text-xs text-slate-500 mt-2 font-medium">{{ gw.desc }}</p>

                            <!-- Info de chaves configuradas -->
                            <div v-if="gw.id !== 'interno' && gw.configured" class="mt-3 p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-[var(--border-color)]">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <KeyIcon class="w-3 h-3" /> Chave configurada
                                </p>
                            </div>

                            <div class="mt-6 flex items-center gap-2 justify-end">
                                <!-- Botão Ativar (se configurado e não ativo) -->
                                <button v-if="gw.id !== 'interno' && gw.configured && !gw.active" @click="activateGateway(gw.id)" class="px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-500 text-white hover:bg-emerald-600 shadow-lg shadow-emerald-500/20 transition-all">
                                    Ativar
                                </button>
                                <!-- Botão Gerenciar (interno ativo) -->
                                <PrimaryButton v-if="gw.id === 'interno' && gw.active" @click="openModal('interno')" class="!px-6 !py-2 !rounded-full !text-[10px] !font-black !uppercase !tracking-widest shadow-lg shadow-primary-dynamic/20">
                                    Gerenciar
                                </PrimaryButton>
                                <!-- Botão Configurar (gateways externos) -->
                                <PrimaryButton v-if="gw.id !== 'interno'" @click="openModal(gw.id)" class="!px-6 !py-2 !rounded-full !text-[10px] !font-black !uppercase !tracking-widest" :class="gw.active ? 'shadow-lg shadow-primary-dynamic/20' : '!bg-slate-200 hover:!bg-slate-300 !text-slate-700 dark:!bg-slate-700 dark:!text-slate-300 dark:hover:!bg-slate-600 !shadow-none'">
                                    {{ gw.configured ? 'Editar' : 'Configurar' }}
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nota de aviso -->
                <div class="mt-8 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-2xl p-6 flex items-start gap-4">
                    <ExclamationTriangleIcon class="w-6 h-6 text-amber-500 shrink-0" />
                    <div>
                        <h4 class="text-sm font-black text-amber-900 dark:text-amber-300 tracking-tight uppercase">Configuração Pendente</h4>
                        <p class="text-xs text-amber-800 dark:text-amber-400 mt-1 font-medium leading-relaxed">
                            Apenas a Cobrança Interna está ativa no momento. Para emitir cobranças com código de barras, PIX copia e cola, ou links de pagamento para os associados, configure e ative um Gateway externo.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: Cobrança Interna -->
        <Modal :show="activeModal === 'interno'" @close="closeModal" max-width="md">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <p class="text-[10px] font-black text-blue-500 uppercase tracking-[0.2em] mb-1">Gateway</p>
                        <h2 class="text-xl font-black text-[var(--text-heading)] tracking-tighter">Cobrança Interna</h2>
                    </div>
                    <button @click="closeModal" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>
                <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-xl p-5">
                    <div class="flex items-start gap-3">
                        <CheckCircleIcon class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" />
                        <div>
                            <p class="text-sm font-bold text-blue-800 dark:text-blue-300">Este gateway está ativo por padrão.</p>
                            <p class="text-xs text-blue-700 dark:text-blue-400 mt-1.5 font-medium leading-relaxed">
                                O sistema gera títulos de cobrança internamente, e o controle de pagamento é feito de forma manual pela equipe administrativa. Não são enviados boletos ou links automatizados.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal" class="!rounded-full !py-3 !px-6">Fechar</SecondaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modal: Asaas -->
        <Modal :show="activeModal === 'asaas'" @close="closeModal" max-width="lg">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <p class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em] mb-1">Gateway</p>
                        <h2 class="text-xl font-black text-[var(--text-heading)] tracking-tighter">Configurar Asaas</h2>
                    </div>
                    <button @click="closeModal" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-5">
                    <div>
                        <InputLabel value="Chave de API (API Key) *" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                        <TextInput v-model="form.asaas.api_key" type="password" class="mt-1.5 block w-full font-mono" placeholder="$aact_YTU5YTE0M2M2MWM..." />
                        <InputError :message="form.errors['asaas.api_key']" class="mt-1" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <InputLabel value="Ambiente *" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                            <select v-model="form.asaas.environment" class="mt-1.5 w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] focus:ring-emerald-500 focus:border-emerald-500 py-2.5 px-3 text-sm font-medium">
                                <option value="sandbox">🧪 Sandbox (Testes)</option>
                                <option value="production">🚀 Produção</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Token do Webhook (Opcional)" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                            <TextInput v-model="form.asaas.webhook_token" type="password" class="mt-1.5 block w-full font-mono" placeholder="Token secreto..." />
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-800/50 border border-[var(--border-color)] rounded-xl p-4 mt-2">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">URL do Webhook</p>
                        <code class="text-xs font-mono text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-900 px-3 py-1.5 rounded-lg block overflow-x-auto">
                            {{ baseUrl }}/api/webhooks/asaas
                        </code>
                    </div>

                    <div class="pt-4 flex justify-end gap-3 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="closeModal" type="button" class="!rounded-full !py-3 !px-6">Cancelar</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing" class="!rounded-full !py-3 !px-6 !bg-emerald-500 hover:!bg-emerald-600 shadow-lg shadow-emerald-500/20">
                            {{ form.processing ? 'Salvando...' : 'Salvar Configuração' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal: Stripe -->
        <Modal :show="activeModal === 'stripe'" @close="closeModal" max-width="lg">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <p class="text-[10px] font-black text-violet-500 uppercase tracking-[0.2em] mb-1">Gateway</p>
                        <h2 class="text-xl font-black text-[var(--text-heading)] tracking-tighter">Configurar Stripe</h2>
                    </div>
                    <button @click="closeModal" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-5">
                    <div>
                        <InputLabel value="Public Key *" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                        <TextInput v-model="form.stripe.public_key" type="password" class="mt-1.5 block w-full font-mono" placeholder="pk_live_..." />
                        <InputError :message="form.errors['stripe.public_key']" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Secret Key *" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                        <TextInput v-model="form.stripe.secret_key" type="password" class="mt-1.5 block w-full font-mono" placeholder="sk_live_..." />
                        <InputError :message="form.errors['stripe.secret_key']" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Webhook Signing Secret (Opcional)" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                        <TextInput v-model="form.stripe.webhook_secret" type="password" class="mt-1.5 block w-full font-mono" placeholder="whsec_..." />
                    </div>

                    <div class="pt-4 flex justify-end gap-3 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="closeModal" type="button" class="!rounded-full !py-3 !px-6">Cancelar</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing" class="!rounded-full !py-3 !px-6 !bg-violet-500 hover:!bg-violet-600 shadow-lg shadow-violet-500/20">
                            {{ form.processing ? 'Salvando...' : 'Salvar Configuração' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal: Mercado Pago -->
        <Modal :show="activeModal === 'mercadopago'" @close="closeModal" max-width="lg">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <p class="text-[10px] font-black text-sky-500 uppercase tracking-[0.2em] mb-1">Gateway</p>
                        <h2 class="text-xl font-black text-[var(--text-heading)] tracking-tighter">Configurar Mercado Pago</h2>
                    </div>
                    <button @click="closeModal" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-5">
                    <div>
                        <InputLabel value="Access Token *" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                        <TextInput v-model="form.mercadopago.access_token" type="password" class="mt-1.5 block w-full font-mono" placeholder="APP_USR-..." />
                        <InputError :message="form.errors['mercadopago.access_token']" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Public Key *" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                        <TextInput v-model="form.mercadopago.public_key" type="password" class="mt-1.5 block w-full font-mono" placeholder="APP_USR-..." />
                        <InputError :message="form.errors['mercadopago.public_key']" class="mt-1" />
                    </div>

                    <div class="pt-4 flex justify-end gap-3 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="closeModal" type="button" class="!rounded-full !py-3 !px-6">Cancelar</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing" class="!rounded-full !py-3 !px-6 !bg-sky-500 hover:!bg-sky-600 shadow-lg shadow-sky-500/20">
                            {{ form.processing ? 'Salvando...' : 'Salvar Configuração' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
