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
    ShieldCheckIcon, 
    IdentificationIcon,
    CurrencyDollarIcon,
    PencilSquareIcon,
    TrashIcon,
    XMarkIcon,
    Squares2X2Icon,
    CheckIcon
} from '@heroicons/vue/24/outline';
import { ref } from 'vue';
import { maskDirective as vMask } from '@/Directives/mask';

const props = defineProps({
    planos: Array,
});

const showModal = ref(false);
const editingPlano = ref(null);

const form = useForm({
    nome: '',
    coberturas: [],
    faixas_etarias: [],
});

const newFaixa = ref({
    de: '',
    ate: '',
    valor: ''
});

const addFaixa = () => {
    if (!newFaixa.value.de || !newFaixa.value.ate || !newFaixa.value.valor) return;
    
    form.faixas_etarias.push({ ...newFaixa.value });
    newFaixa.value = { de: '', ate: '', valor: '' };
};

const removeFaixa = (index) => {
    form.faixas_etarias.splice(index, 1);
};

const openCreateModal = () => {
    editingPlano.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (plano) => {
    editingPlano.value = plano;
    form.nome = plano.nome;
    form.coberturas = Array.isArray(plano.coberturas) ? plano.coberturas : [];
    form.faixas_etarias = Array.isArray(plano.faixas_etarias) ? [...plano.faixas_etarias] : [];
    showModal.value = true;
};

const submit = () => {
    if (editingPlano.value) {
        form.put(route('saude.planos.update', editingPlano.value.id), {
            onSuccess: () => { showModal.value = false; form.reset(); }
        });
    } else {
        form.post(route('saude.planos.store'), {
            onSuccess: () => { showModal.value = false; form.reset(); }
        });
    }
};

const deletePlano = (id) => {
    if (confirm('Deseja realmente excluir este plano?')) {
        form.delete(route('saude.planos.destroy', id));
    }
};
</script>

<template>
    <Head title="Planos de Saúde" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <Breadcrumbs :items="[{ label: 'Saúde' }, { label: 'Planos' }]" />
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Planos de Saúde</h2>
                    <p class="text-sm text-[var(--text-muted)] mt-0.5">Cadastre e gerencie os planos e coberturas oferecidos aos associados.</p>
                </div>
                <div>
                    <PrimaryButton @click="openCreateModal" class="shadow-xl">
                        <PlusIcon class="w-4 h-4 mr-2" />
                        Novo Plano
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12">
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="plano in planos" :key="plano.id" class="zynix-card group hover:scale-[1.02] transition-all duration-300 flex flex-col">
                        <div class="flex items-start justify-between mb-6">
                            <div class="w-12 h-12 p-3 rounded-2xl bg-primary-dynamic/10 text-primary-dynamic shadow-inner group-hover:bg-primary-dynamic/20 transition-all">
                                <ShieldCheckIcon class="w-full h-full" />
                            </div>
                             <div class="flex items-center space-x-1">
                                <button @click="openEditModal(plano)" class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-xl transition-all" title="Editar">
                                    <PencilSquareIcon class="w-4 h-4" />
                                </button>
                                <button @click="deletePlano(plano.id)" class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-all" title="Excluir">
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <h3 class="text-xl font-black text-[var(--text-heading)] tracking-tight">{{ plano.nome }}</h3>
                            <div class="flex flex-wrap gap-1.5 mt-3">
                                <span v-for="cob in plano.coberturas" :key="cob" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded text-[9px] font-black uppercase tracking-widest border border-slate-200/50 dark:border-slate-700/50">
                                    {{ cob }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-auto pt-6 border-t border-[var(--border-color)]">
                            <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-4">Tabela de Preços</p>
                            <div class="grid grid-cols-2 gap-3">
                                <div v-for="faixa in plano.faixas_etarias.slice(0, 2)" :key="faixa.de" class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-700/50">
                                    <p class="text-[8px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">{{ faixa.de }}-{{ faixa.ate }} anos</p>
                                    <p class="text-sm font-black text-[var(--text-heading)] tracking-tight">R$ {{ parseFloat(faixa.valor).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="planos.length === 0" class="col-span-full py-24 zynix-card border-dashed flex flex-col items-center justify-center text-center">
                        <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-6">
                            <Squares2X2Icon class="w-10 h-10 text-slate-300" />
                        </div>
                        <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">Nenhum plano cadastrado</h3>
                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-2 max-w-xs leading-relaxed">
                            Comece criando seu primeiro plano de saúde para os cooperados.
                        </p>
                    </div>
                </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showModal" @close="showModal = false" max-width="2xl">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <p class="text-[10px] font-black text-primary-dynamic uppercase tracking-[0.2em] mb-1">Configuração</p>
                        <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tighter">
                            {{ editingPlano ? 'Editar Plano' : 'Novo Plano de Saúde' }}
                        </h2>
                    </div>
                    <button @click="showModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>
                
                <form @submit.prevent="submit" class="space-y-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div>
                                <InputLabel for="nome" value="Nome do Plano *" />
                                <TextInput id="nome" v-model="form.nome" type="text" class="block w-full" placeholder="Ex: Master Saúde Plus" required autofocus />
                                <InputError class="mt-2" :message="form.errors.nome" />
                            </div>

                            <div>
                                <InputLabel for="coberturas" value="Coberturas (Um por linha)" />
                                <textarea 
                                    id="coberturas"
                                    :value="form.coberturas.join('\n')" 
                                    @input="e => form.coberturas = e.target.value.split('\n').filter(l => l.trim())"
                                    rows="6" 
                                    class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic py-4 px-5 text-sm font-medium transition-all"
                                    placeholder="Ex: Consultas&#10;Exames&#10;Internação"
                                ></textarea>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="p-6 bg-[var(--bg-main)] rounded-xl border border-[var(--border-color)]">
                                <p class="text-[10px] font-black text-[var(--text-heading)] uppercase tracking-[0.2em] mb-4">Adicionar Faixa Etária</p>
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">De (Anos)</label>
                                        <input v-model="newFaixa.de" type="number" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-main)] px-4 py-3 text-sm focus:ring-primary-dynamic" placeholder="0" />
                                    </div>
                                    <div>
                                        <label class="block text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Até (Anos)</label>
                                        <input v-model="newFaixa.ate" type="number" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-main)] px-4 py-3 text-sm focus:ring-primary-dynamic" placeholder="18" />
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Valor (R$)</label>
                                        <input v-model="newFaixa.valor" v-mask="'currency'" type="text" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-main)] px-4 py-3 text-sm focus:ring-primary-dynamic font-black text-primary-dynamic" placeholder="R$ 0,00" />
                                    </div>
                                </div>
                                <button type="button" @click="addFaixa" class="w-full py-3 bg-primary-dynamic text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-primary-dynamic/20 hover:scale-[1.02] active:scale-95 transition-all">
                                    Confirmar Valor
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Listagem Integrada (Full Width) -->
                    <div class="pt-8 border-t border-[var(--border-color)]">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-[11px] font-black text-[var(--text-heading)] uppercase tracking-[0.3em]">Faixas Cadastradas</p>
                            <span class="px-2 py-0.5 bg-primary-dynamic/10 text-primary-dynamic rounded text-[10px] font-black">{{ form.faixas_etarias.length }} itens</span>
                        </div>
                        
                        <div class="space-y-0 border border-[var(--border-color)] rounded-2xl overflow-hidden bg-[var(--bg-main)]/30">
                            <div v-if="form.faixas_etarias.length === 0" class="py-12 text-center">
                                <p class="text-[10px] font-black text-slate-400 dark:text-slate-600 uppercase tracking-widest">Nenhuma faixa adicionada</p>
                            </div>
                            <div v-for="(faixa, index) in form.faixas_etarias" :key="index" class="group flex items-center justify-between px-6 py-4 border-b border-[var(--border-color)] last:border-0 hover:bg-primary-dynamic/5 transition-colors">
                                <div class="flex items-center gap-6">
                                    <div class="w-10 h-10 rounded-xl bg-[var(--bg-card)] border border-[var(--border-color)] flex items-center justify-center shadow-sm">
                                        <CurrencyDollarIcon class="w-5 h-5 text-primary-dynamic" />
                                    </div>
                                    <div class="flex items-center gap-8">
                                        <div>
                                            <p class="text-[8px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-0.5">Idade</p>
                                            <p class="text-sm font-black text-[var(--text-main)] tracking-tight">{{ faixa.de }}-{{ faixa.ate }} anos</p>
                                        </div>
                                        <div>
                                            <p class="text-[8px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-0.5">Mensalidade</p>
                                            <p class="text-sm font-black text-primary-dynamic tracking-tight">R$ {{ faixa.valor }}</p>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" @click="removeFaixa(index)" class="p-2.5 text-slate-400 hover:text-rose-500 hover:bg-rose-500/10 rounded-xl transition-all opacity-0 group-hover:opacity-100">
                                    <TrashIcon class="w-4.5 h-4.5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 flex items-center justify-end space-x-3 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="showModal = false" type="button" class="!rounded-full !py-4 !px-8 text-slate-600">
                            <XMarkIcon class="w-4 h-4 mr-2" /> Cancelar
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing" class="!rounded-full !py-4 !px-8 shadow-xl shadow-primary-dynamic/20">
                            <CheckIcon v-if="editingPlano" class="w-4 h-4 mr-2" />
                            <PlusIcon v-else class="w-4 h-4 mr-2" />
                            {{ editingPlano ? 'Atualizar Plano' : 'Criar Plano de Saúde' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
