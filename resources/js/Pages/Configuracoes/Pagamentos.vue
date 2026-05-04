<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { CreditCardIcon, CheckCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const gateways = [
    { id: 'interno', name: 'Cobrança Interna (Sistema)', desc: 'Apenas geração de títulos e controle manual no sistema.', active: true },
    { id: 'asaas', name: 'Integração Asaas', desc: 'Emissão automatizada de Boletos e PIX com retorno instantâneo.', active: false },
    { id: 'stripe', name: 'Integração Stripe', desc: 'Cobrança via Cartão de Crédito recorrente.', active: false },
    { id: 'mercadopago', name: 'Mercado Pago', desc: 'Geração de PIX e split de pagamentos.', active: false }
];

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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div v-for="gw in gateways" :key="gw.id" class="zynix-card relative overflow-hidden group">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 rounded-2xl bg-primary-dynamic/10 text-primary-dynamic flex items-center justify-center">
                                    <CreditCardIcon class="w-6 h-6" />
                                </div>
                                <div>
                                    <span v-if="gw.active" class="inline-flex items-center gap-1 text-[10px] bg-emerald-50 text-emerald-600 font-black px-3 py-1 rounded-full uppercase tracking-widest border border-emerald-200">
                                        <CheckCircleIcon class="w-3 h-3" /> Ativo
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 text-[10px] bg-slate-100 text-slate-500 font-black px-3 py-1 rounded-full uppercase tracking-widest border border-slate-200">
                                        Desabilitado
                                    </span>
                                </div>
                            </div>
                            <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">{{ gw.name }}</h3>
                            <p class="text-xs text-slate-500 mt-2 font-medium">{{ gw.desc }}</p>
                            
                            <div class="mt-6 flex items-center justify-end">
                                <PrimaryButton v-if="!gw.active" class="!px-6 !py-2 !rounded-full !text-[10px] !font-black !uppercase !tracking-widest !bg-slate-200 hover:!bg-slate-300 !text-slate-700 shadow-none">
                                    Configurar
                                </PrimaryButton>
                                <PrimaryButton v-else class="!px-6 !py-2 !rounded-full !text-[10px] !font-black !uppercase !tracking-widest shadow-lg shadow-primary-dynamic/20">
                                    Gerenciar
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 bg-amber-50 border border-amber-200 rounded-2xl p-6 flex items-start gap-4">
                    <ExclamationTriangleIcon class="w-6 h-6 text-amber-500 shrink-0" />
                    <div>
                        <h4 class="text-sm font-black text-amber-900 tracking-tight uppercase">Configuração Pendente</h4>
                        <p class="text-xs text-amber-800 mt-1 font-medium leading-relaxed">
                            Apenas a Cobrança Interna está ativa no momento. Para emitir cobranças com código de barras, PIX copia e cola, ou links de pagamento para os associados, você precisa configurar um Gateway externo (Asaas, Stripe ou Mercado Pago).
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
