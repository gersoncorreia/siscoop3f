<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { 
    PlusIcon, 
    BanknotesIcon, 
    BuildingLibraryIcon,
    CreditCardIcon,
    PencilSquareIcon,
    TrashIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';
import { ref } from 'vue';

const props = defineProps({
    contas: Array,
});

const showModal = ref(false);
const editingConta = ref(null);

const form = useForm({
    banco: '',
    agencia: '',
    conta: '',
    saldo: 0,
});

const openCreateModal = () => {
    editingConta.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (conta) => {
    editingConta.value = conta;
    form.banco = conta.banco;
    form.agencia = conta.agencia;
    form.conta = conta.conta;
    form.saldo = conta.saldo;
    showModal.value = true;
};

const submit = () => {
    if (editingConta.value) {
        form.put(route('financeiro.contas.update', editingConta.value.id), {
            onSuccess: () => { showModal.value = false; form.reset(); }
        });
    } else {
        form.post(route('financeiro.contas.store'), {
            onSuccess: () => { showModal.value = false; form.reset(); }
        });
    }
};

const deleteConta = (id) => {
    if (confirm('Deseja realmente excluir esta conta?')) {
        form.delete(route('financeiro.contas.destroy', id));
    }
};
</script>

<template>
    <Head title="Contas Bancárias" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <Breadcrumbs :items="[{ label: 'Financeiro', href: '/financeiro' }, { label: 'Contas Bancárias' }]" />
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Contas Bancárias</h2>
                    <p class="text-sm text-[var(--text-muted)] mt-0.5">Gerencie as contas financeiras e caixas da instituição.</p>
                </div>
                <div>
                    <PrimaryButton @click="openCreateModal" class="shadow-xl">
                        <PlusIcon class="w-4 h-4 mr-2" />
                        Nova Conta
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12">
                
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="zynix-card group hover:scale-[1.02] transition-all duration-300">
                        <div class="flex items-center gap-4">
                            <div class="p-4 rounded-2xl bg-primary-dynamic/10 text-primary-dynamic shadow-inner group-hover:bg-primary-dynamic/20 transition-colors">
                                <BuildingLibraryIcon class="w-8 h-8" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Total em Contas</p>
                                <p class="text-2xl font-black text-[var(--text-heading)] tracking-tighter mt-1">
                                    R$ {{ contas.reduce((acc, c) => acc + parseFloat(c.saldo), 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accounts Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="conta in contas" :key="conta.id" class="zynix-card group hover:scale-[1.02] transition-all duration-300 flex flex-col">
                        <div class="flex items-start justify-between mb-6">
                            <div class="w-12 h-12 p-3 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 group-hover:text-primary-dynamic group-hover:bg-primary-dynamic/10 transition-all shadow-inner">
                                <CreditCardIcon class="w-full h-full" />
                            </div>
                            <div class="flex items-center space-x-1">
                                <button @click="openEditModal(conta)" class="p-2 text-slate-400 hover:text-primary-dynamic transition-all rounded-full hover:bg-primary-dynamic/10">
                                    <PencilSquareIcon class="w-4 h-4" />
                                </button>
                                <button @click="deleteConta(conta.id)" class="p-2 text-slate-400 hover:text-rose-500 transition-all rounded-full hover:bg-rose-500/10">
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">{{ conta.banco }}</h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Agência</span>
                                <span class="text-xs font-mono font-medium text-slate-600 dark:text-slate-400">{{ conta.agencia }}</span>
                                <span class="text-slate-300 dark:text-slate-700">|</span>
                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Conta</span>
                                <span class="text-xs font-mono font-medium text-slate-600 dark:text-slate-400">{{ conta.conta }}</span>
                            </div>
                        </div>

                        <div class="mt-auto pt-4 border-t border-[var(--border-color)] flex items-center justify-between">
                            <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Saldo Atual</span>
                            <span class="text-xl font-black text-emerald-500 tracking-tighter">
                                R$ {{ parseFloat(conta.saldo).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                            </span>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="contas.length === 0" class="col-span-full py-20 zynix-card border-dashed flex flex-col items-center justify-center text-center">
                        <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-6">
                            <BuildingLibraryIcon class="w-10 h-10 text-slate-300" />
                        </div>
                        <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">Nenhuma conta cadastrada</h3>
                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-2 max-w-xs leading-relaxed">
                            Organize sua vida financeira adicionando suas contas bancárias aqui.
                        </p>
                    </div>
                </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showModal" @close="showModal = false" max-width="lg">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <p class="text-[10px] font-black text-primary-dynamic uppercase tracking-[0.2em] mb-1">Configuração</p>
                        <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tighter">
                            {{ editingConta ? 'Editar Conta' : 'Nova Conta' }}
                        </h2>
                    </div>
                    <button @click="showModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <div class="space-y-6">
                        <div>
                            <InputLabel for="banco" value="Instituição Financeira *" />
                            <TextInput id="banco" v-model="form.banco" type="text" class="block w-full bg-[var(--bg-main)]" placeholder="Ex: Banco do Brasil, Itaú..." required autofocus />
                            <InputError class="mt-2" :message="form.errors.banco" />
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="agencia" value="Agência *" />
                                <TextInput id="agencia" v-model="form.agencia" type="text" class="block w-full bg-[var(--bg-main)]" placeholder="0001-0" required />
                                <InputError class="mt-2" :message="form.errors.agencia" />
                            </div>
                            <div>
                                <InputLabel for="conta" value="Conta *" />
                                <TextInput id="conta" v-model="form.conta" type="text" class="block w-full bg-[var(--bg-main)]" placeholder="12345-6" required />
                                <InputError class="mt-2" :message="form.errors.conta" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="saldo" value="Saldo Inicial (R$)" />
                            <TextInput id="saldo" v-model="form.saldo" type="number" step="0.01" class="block w-full bg-[var(--bg-main)]" required />
                            <InputError class="mt-2" :message="form.errors.saldo" />
                        </div>
                    </div>

                    <div class="pt-8 flex items-center justify-end space-x-3 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="showModal = false" type="button" class="!rounded-full !py-4 !px-8">Cancelar</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing" class="!rounded-full !py-4 !px-8 shadow-xl shadow-primary-dynamic/20">
                            {{ editingConta ? 'Salvar Alterações' : 'Cadastrar Conta' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
