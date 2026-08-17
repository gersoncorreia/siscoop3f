<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { ref } from 'vue';
import { 
    CheckIcon, 
    XMarkIcon, 
    EyeIcon, 
    UserIcon,
    ClockIcon,
    BanknotesIcon,
    CreditCardIcon,
    QrCodeIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    pendentes: Object
});

const selectedItem = ref(null);
const showAprovarModal = ref(false);

const aprovarForm = useForm({
    forma_pagamento: 'dinheiro',
    observacoes_pagamento: '',
});

function openAprovarModal(item) {
    selectedItem.value = item;
    aprovarForm.forma_pagamento = 'dinheiro';
    aprovarForm.observacoes_pagamento = '';
    showAprovarModal.value = true;
}

function submitAprovar() {
    if (!selectedItem.value) return;
    aprovarForm.post(route('associados.adesoes.aprovar', selectedItem.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showAprovarModal.value = false;
            selectedItem.value = null;
        }
    });
}

function rejeitar(id) {
    if (confirm('Tem certeza que deseja recusar esta solicitação de adesão?')) {
        router.post(route('associados.adesoes.rejeitar', id));
    }
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('pt-BR');
}
</script>

<template>
    <Head title="Solicitações de Adesão" />

    <AuthenticatedLayout>
        <template #header>
            <Breadcrumbs :items="[{ label: 'Associados', href: '/associados' }, { label: 'Adesões' }]" />
            <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Solicitações de Adesão</h2>
            <p class="text-sm text-[var(--text-muted)] mt-0.5">Analise e aprove as pré-adesões enviadas pelo formulário público.</p>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="zynix-card group hover:scale-[1.02] transition-all duration-300">
                    <div class="flex items-center gap-4">
                        <div class="p-4 rounded-2xl bg-amber-500/10 text-amber-500 shadow-inner group-hover:bg-amber-500/20 transition-colors">
                            <ClockIcon class="w-8 h-8" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Aguardando Análise</p>
                            <p class="text-2xl font-black text-[var(--text-heading)] tracking-tighter mt-1">{{ pendentes.total }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Table -->
            <div class="zynix-table-wrapper">
                <div v-if="pendentes.data.length === 0" class="text-center py-20">
                    <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6">
                        <ClockIcon class="w-10 h-10 text-slate-300" />
                    </div>
                    <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">Nenhuma solicitação pendente</h3>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-2 max-w-xs mx-auto leading-relaxed">
                        Novas adesões aparecerão aqui conforme forem enviadas pelos clientes por meio do link público.
                    </p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="zynix-table w-full">
                        <thead>
                            <tr>
                                <th class="pl-8">Associado</th>
                                <th class="text-center">Documento</th>
                                <th class="text-center">Solicitado em</th>
                                <th class="text-right pr-8">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in pendentes.data" :key="item.id">
                                <td class="pl-8">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-9 h-9 p-2 rounded-xl bg-primary-dynamic/10 text-primary-dynamic shadow-inner">
                                            <UserIcon class="w-full h-full" />
                                        </div>
                                        <div class="ml-4">
                                            <div class="font-black text-[var(--text-heading)] tracking-tight">{{ item.nome }}</div>
                                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-tight">{{ item.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="inline-flex items-center gap-2 font-mono text-xs text-slate-600 dark:text-slate-400">
                                        {{ item.cpf }}
                                    </div>
                                </td>
                                <td class="text-center font-medium">
                                    {{ formatDate(item.created_at) }}
                                </td>
                                <td class="text-right pr-8 whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-1">
                                        <button 
                                            @click="openAprovarModal(item)" 
                                            class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5 shadow-md shadow-emerald-500/20 transition-all"
                                            title="Aprovar e Dar Baixa"
                                        >
                                            <CheckIcon class="w-4 h-4" />
                                            Aprovar & Dar Baixa
                                        </button>
                                        <button 
                                            @click="rejeitar(item.id)" 
                                            class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-all"
                                            title="Rejeitar"
                                        >
                                            <XMarkIcon class="w-4 h-4" />
                                        </button>
                                        <Link 
                                            :href="route('associados.edit', item.id)"
                                            class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-xl transition-all"
                                            title="Ver Detalhes"
                                        >
                                            <EyeIcon class="w-4 h-4" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="pendentes.data.length > 0 && pendentes.links" class="p-6 border-t border-[var(--border-color)] flex items-center justify-between">
                     <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                         Mostrando {{ pendentes.from }}-{{ pendentes.to }} de {{ pendentes.total }}
                     </div>
                </div>
            </div>

            <!-- Modal Aprovação & Baixa Manual -->
            <Modal :show="showAprovarModal" @close="showAprovarModal = false" max-width="md">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <p class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em] mb-1">Aprovação de Adesão</p>
                            <h2 class="text-xl font-black text-[var(--text-heading)] tracking-tighter">Aprovar & Confirmar Pagamento</h2>
                        </div>
                        <button @click="showAprovarModal = false" class="p-2 text-slate-400 hover:text-slate-600 transition-colors">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <div v-if="selectedItem" class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl mb-5 border border-[var(--border-color)]">
                        <p class="text-xs font-bold text-[var(--text-heading)]">{{ selectedItem.nome }}</p>
                        <p class="text-[11px] text-slate-500">CPF: {{ selectedItem.cpf }} | {{ selectedItem.email }}</p>
                    </div>

                    <form @submit.prevent="submitAprovar" class="space-y-4">
                        <div>
                            <InputLabel value="Forma de Pagamento Recebida *" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                            <select v-model="aprovarForm.forma_pagamento" class="mt-1.5 w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] py-2.5 px-3 text-xs font-bold">
                                <option value="dinheiro">💵 Dinheiro em Espécie (Recepção)</option>
                                <option value="cartao_debito">💳 Cartão de Débito (Maquininha)</option>
                                <option value="cartao_credito">💳 Cartão de Crédito (Maquininha)</option>
                                <option value="pix_manual">📲 PIX Manual (Chave/QRCode da Entidade)</option>
                                <option value="transferencia">🏦 Transferência Bancária / TED</option>
                            </select>
                        </div>

                        <div>
                            <InputLabel value="Observações / Código da Transação (Opcional)" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                            <input 
                                type="text" 
                                v-model="aprovarForm.observacoes_pagamento" 
                                class="mt-1.5 w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] text-xs py-2.5 px-3" 
                                placeholder="Ex: Autorização maquininha #98231"
                            />
                        </div>

                        <div class="pt-4 flex justify-end gap-3 border-t border-[var(--border-color)]">
                            <SecondaryButton @click="showAprovarModal = false" type="button" class="!rounded-full !py-3 !px-6">Cancelar</SecondaryButton>
                            <PrimaryButton type="submit" :disabled="aprovarForm.processing" class="!rounded-full !py-3 !px-6 !bg-emerald-500 hover:!bg-emerald-600 shadow-lg shadow-emerald-500/20">
                                {{ aprovarForm.processing ? 'Aprovando...' : 'Aprovar & Confirmar' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>
        </div>
    </AuthenticatedLayout>
</template>
