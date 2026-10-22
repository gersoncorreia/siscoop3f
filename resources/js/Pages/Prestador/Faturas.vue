<script setup>
import PrestadorLayout from '@/Layouts/PrestadorLayout.vue';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    DocumentTextIcon,
    ArrowUpTrayIcon,
    CurrencyDollarIcon,
    ClockIcon,
    CheckCircleIcon,
    XMarkIcon,
    ArrowDownTrayIcon,
    CheckIcon
} from '@heroicons/vue/24/outline';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    prestador: Object,
    faturas: Array,
});

const page = usePage();
const primaryColor = computed(() => page.props.tenant?.primary_color || '#2563eb');

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};

const statusConfig = {
    'enviada': { color: 'text-amber-500', bg: 'bg-amber-50 dark:bg-amber-500/10', icon: ClockIcon, label: 'Em Análise' },
    'em_analise': { color: 'text-amber-500', bg: 'bg-amber-50 dark:bg-amber-500/10', icon: ClockIcon, label: 'Em Análise' },
    'em_auditoria': { color: 'text-blue-500', bg: 'bg-blue-50 dark:bg-blue-500/10', icon: DocumentTextIcon, label: 'Em Auditoria' },
    'paga': { color: 'text-emerald-500', bg: 'bg-emerald-50 dark:bg-emerald-500/10', icon: CheckCircleIcon, label: 'Paga' }
};

const getStatus = (status) => statusConfig[status] || statusConfig['enviada'];

// --- Modal de Envio de Fatura ---
const isModalOpen = ref(false);

const form = useForm({
    mes_referencia: '',
    valor_total: '',
    arquivo_guia: null,
});

const openModal = () => {
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const handleFileUpload = (e) => {
    form.arquivo_guia = e.target.files[0];
};

const submitFatura = () => {
    form.post(route('prestador.faturas.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Faturamento Mensal" />

    <PrestadorLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 w-full">
                <div>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">Faturamento e Repasses</h2>
                    <p class="text-[12px] text-[var(--text-muted)] mt-0.5 font-bold">
                        Envie suas notas fiscais até o dia 05 e acompanhe os pagamentos.
                    </p>
                </div>
                <button @click="openModal" class="bg-primary-dynamic text-white text-sm font-black px-6 py-3 rounded-xl shadow-lg shadow-primary-dynamic/20 flex items-center hover:opacity-90 transition-opacity">
                    <ArrowUpTrayIcon class="w-5 h-5 mr-2" /> Enviar Nova Fatura
                </button>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            
            <!-- Information Banner -->
            <div class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 p-5 rounded-2xl flex items-start gap-4">
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 shrink-0">
                    <CurrencyDollarIcon class="w-6 h-6 text-emerald-500" />
                </div>
                <div>
                    <h3 class="text-sm font-black text-[var(--text-heading)]">Como funciona o faturamento?</h3>
                    <p class="text-sm text-[var(--text-muted)] mt-1 font-medium leading-relaxed max-w-3xl">
                        Todas as solicitações de serviços autorizadas e realizadas devem ser consolidadas em uma única fatura mensal. 
                        <strong>Envie a nota fiscal até o dia 05 do mês corrente</strong>, referente aos atendimentos do mês anterior. 
                        O pagamento (repasse) será programado pela cooperativa para o <strong>dia 05 do mês subsequente</strong>, após a auditoria.
                    </p>
                </div>
            </div>

            <!-- Tabela de Faturas -->
            <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-color)] overflow-hidden">
                <div class="px-6 py-5 border-b border-[var(--border-color)] flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
                    <h3 class="text-base font-black text-[var(--text-heading)]">Histórico de Repasses</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[var(--border-color)]">
                        <thead class="bg-slate-50/50 dark:bg-slate-800/50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Mês Referência</th>
                                <th scope="col" class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Valor Total</th>
                                <th scope="col" class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                                <th scope="col" class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Data Pagamento</th>
                                <th scope="col" class="px-6 py-4 text-right pr-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Anexo da Nota</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border-color)] bg-transparent">
                            <tr v-for="fatura in faturas" :key="fatura.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-black text-[var(--text-heading)]">{{ fatura.mes_referencia }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-[var(--text-heading)]">{{ formatCurrency(fatura.valor_total) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div :class="['inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest', getStatus(fatura.status).bg, getStatus(fatura.status).color]">
                                        <component :is="getStatus(fatura.status).icon" class="w-3 h-3 mr-1.5" />
                                        {{ getStatus(fatura.status).label }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-[var(--text-muted)]">
                                        {{ fatura.data_pagamento ? new Date(fatura.data_pagamento).toLocaleDateString('pt-BR') : 'Aguardando Auditoria' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right pr-6">
                                    <a 
                                        v-if="fatura.arquivo_guia" 
                                        :href="'/storage/' + fatura.arquivo_guia" 
                                        target="_blank"
                                        class="px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-primary-dynamic hover:text-white text-xs font-bold transition-colors inline-flex items-center gap-1.5"
                                    >
                                        <ArrowDownTrayIcon class="w-3.5 h-3.5" />
                                        Ver Nota Fiscal
                                    </a>
                                    <span v-else class="text-xs text-slate-400 font-medium">Sem anexo</span>
                                </td>
                            </tr>
                            <tr v-if="faturas.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <DocumentTextIcon class="w-12 h-12 text-slate-200 mx-auto mb-3" />
                                    <p class="text-sm text-[var(--text-muted)] font-bold">Nenhuma fatura enviada ainda.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>

        <!-- Modal Enviar Nova Fatura -->
        <Modal :show="isModalOpen" @close="isModalOpen = false" max-width="md">
            <div class="p-6 md:p-8 space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-[var(--border-color)]">
                    <div>
                        <h3 class="text-lg font-black text-[var(--text-heading)]">Enviar Nova Fatura Mensal</h3>
                        <p class="text-xs text-slate-400">Preencha os dados e anexe o documento da Nota Fiscal.</p>
                    </div>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitFatura" class="space-y-4">
                    <div>
                        <InputLabel for="mes_referencia" value="Mês de Referência / Competência *" class="!text-[11px] !font-black uppercase tracking-wider" />
                        <TextInput id="mes_referencia" v-model="form.mes_referencia" type="text" placeholder="Ex: 08/2026" class="w-full mt-1" required />
                        <InputError :message="form.errors.mes_referencia" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="valor_total" value="Valor Total dos Serviços (R$) *" class="!text-[11px] !font-black uppercase tracking-wider" />
                        <TextInput id="valor_total" v-model="form.valor_total" type="number" step="0.01" min="0.01" placeholder="Ex: 1500.00" class="w-full mt-1" required />
                        <InputError :message="form.errors.valor_total" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="arquivo_guia" value="Nota Fiscal / Demonstrativo (PDF, XML ou Imagem)" class="!text-[11px] !font-black uppercase tracking-wider" />
                        <input id="arquivo_guia" type="file" @change="handleFileUpload" class="w-full mt-1 text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-primary-dynamic/10 file:text-primary-dynamic hover:file:bg-primary-dynamic hover:file:text-white transition-all cursor-pointer" accept=".pdf,.xml,.png,.jpg,.jpeg" />
                        <InputError :message="form.errors.arquivo_guia" class="mt-1" />
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="isModalOpen = false" type="button">Cancelar</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            <CheckIcon class="w-4 h-4 mr-1.5" />
                            {{ form.processing ? 'Enviando...' : 'Enviar Fatura' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </PrestadorLayout>
</template>

<style scoped>
.text-primary-dynamic { color: v-bind(primaryColor); }
.bg-primary-dynamic { background-color: v-bind(primaryColor) !important; }
.border-primary-dynamic { border-color: v-bind(primaryColor) !important; }
</style>
