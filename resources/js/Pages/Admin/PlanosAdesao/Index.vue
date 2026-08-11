<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    PlusIcon, 
    PencilSquareIcon, 
    TrashIcon, 
    XMarkIcon,
    CurrencyDollarIcon,
    ShieldCheckIcon,
    UserGroupIcon,
    CheckIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    planos: Array,
    tipos: Object,
});

const isModalOpen = ref(false);
const isPricesModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const editingPlano = ref(null);
const planoToDelete = ref(null);
const currentPlanoForPrices = ref(null);
const servicosPrices = ref([]);
const newBeneficio = ref('');

const form = useForm({
    nome: '',
    tipo: '',
    valor_adesao: 0,
    valor_mensalidade: 0,
    descricao: '',
    max_dependentes: 0,
    min_membros: 1,
    beneficios: [],
    ativo: true,
});

const openCreateModal = () => {
    editingPlano.value = null;
    form.reset();
    form.beneficios = [];
    isModalOpen.value = true;
};

const openEditModal = (plano) => {
    editingPlano.value = plano;
    form.nome = plano.nome;
    form.tipo = plano.tipo;
    form.valor_adesao = plano.valor_adesao;
    form.valor_mensalidade = plano.valor_mensalidade;
    form.descricao = plano.descricao || '';
    form.max_dependentes = plano.max_dependentes || 0;
    form.min_membros = plano.min_membros || 1;
    form.beneficios = [...(plano.beneficios || [])];
    form.ativo = plano.ativo;
    isModalOpen.value = true;
};

const addBeneficio = () => {
    if (newBeneficio.value.trim()) {
        form.beneficios.push(newBeneficio.value.trim());
        newBeneficio.value = '';
    }
};

const removeBeneficio = (index) => {
    form.beneficios.splice(index, 1);
};

const submit = () => {
    if (editingPlano.value) {
        form.put(route('admin.planos-adesao.update', editingPlano.value.id), {
            onSuccess: () => { isModalOpen.value = false; form.reset(); }
        });
    } else {
        form.post(route('admin.planos-adesao.store'), {
            onSuccess: () => { isModalOpen.value = false; form.reset(); }
        });
    }
};

const confirmDelete = (plano) => {
    planoToDelete.value = plano;
    isDeleteModalOpen.value = true;
};

const deletePlano = () => {
    if (planoToDelete.value) {
        router.delete(route('admin.planos-adesao.destroy', planoToDelete.value.id), {
            onSuccess: () => {
                isDeleteModalOpen.value = false;
                planoToDelete.value = null;
            }
        });
    }
};

const openPricesModal = async (plano) => {
    currentPlanoForPrices.value = plano;
    // Mock fetch for prices - replace with actual fetch to `/api/admin/planos/${plano.id}/precos`
    // const res = await fetch(`/api/admin/planos/${plano.id}/precos`);
    // servicosPrices.value = await res.json();
    isPricesModalOpen.value = true;
};

const savePrices = () => {
    // router.post(route('admin.planos-adesao.precos', currentPlanoForPrices.value.id), { precos: servicosPrices.value })
    isPricesModalOpen.value = false;
};
</script>

<template>
    <Head title="Planos de Adesão" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <Breadcrumbs :items="[{ label: 'Admin' }, { label: 'Planos de Adesão' }]" />
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Planos de Adesão</h2>
                    <p class="text-sm text-[var(--text-muted)] mt-0.5">Gerencie os planos oferecidos aos membros da Fundação.</p>
                </div>
                <div>
                    <PrimaryButton @click="openCreateModal" class="shadow-xl">
                        <PlusIcon class="w-4 h-4 mr-2" />
                        Novo Plano
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="pb-12">
            
            <div v-if="!planos || planos.length === 0" class="zynix-card p-12 text-center">
                <ShieldCheckIcon class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Nenhum plano cadastrado.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="p in planos" :key="p.id" class="zynix-card flex flex-col relative overflow-hidden transition-all hover:shadow-lg hover:border-primary-dynamic/30">
                    <div class="p-6 flex-grow">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-xl font-black text-[var(--text-heading)]">{{ p.nome }}</h3>
                            <span class="px-2 py-1 bg-primary-dynamic/10 text-primary-dynamic text-[9px] font-black uppercase tracking-widest rounded-md border border-primary-dynamic/20">
                                {{ tipos[p.tipo] || p.tipo }}
                            </span>
                        </div>
                        
                        <div class="mb-4">
                            <div class="flex items-end gap-1 text-[var(--text-heading)]">
                                <span class="text-sm font-bold">R$</span>
                                <span class="text-3xl font-black">{{ p.valor_mensalidade }}</span>
                                <span class="text-sm text-slate-400 font-medium">/mês</span>
                            </div>
                            <p class="text-xs font-bold text-slate-500 mt-1">Adesão: R$ {{ p.valor_adesao }}</p>
                        </div>

                        <div class="flex items-center gap-2 mb-4 text-xs font-bold text-slate-600 bg-slate-50 dark:bg-slate-800/50 p-2 rounded-lg">
                            <UserGroupIcon class="w-4 h-4 text-primary-dynamic" />
                            <span>{{ p.total_membros_ativos || 0 }} membros ativos</span>
                        </div>

                        <ul class="space-y-2 mb-6">
                            <li v-for="(ben, i) in (p.beneficios || []).slice(0,4)" :key="i" class="flex items-start gap-2 text-sm text-[var(--text-muted)] font-medium">
                                <CheckIcon class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" />
                                <span>{{ ben }}</span>
                            </li>
                            <li v-if="(p.beneficios || []).length > 4" class="text-xs text-slate-400 font-bold ml-6">
                                + {{ p.beneficios.length - 4 }} outros...
                            </li>
                        </ul>
                    </div>

                    <div class="p-4 bg-slate-50 dark:bg-slate-800/30 border-t border-[var(--border-color)] flex flex-wrap gap-2 justify-between">
                        <div class="flex gap-2">
                            <button @click="openEditModal(p)" class="px-3 py-1.5 text-xs font-bold bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50 transition-all flex items-center gap-1">
                                <PencilSquareIcon class="w-3.5 h-3.5" /> Editar
                            </button>
                            <button @click="openPricesModal(p)" class="px-3 py-1.5 text-xs font-bold bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50 transition-all flex items-center gap-1">
                                <CurrencyDollarIcon class="w-3.5 h-3.5" /> Preços
                            </button>
                        </div>
                        <button @click="confirmDelete(p)" class="px-3 py-1.5 text-xs font-bold text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-lg transition-all" title="Excluir">
                            <TrashIcon class="w-3.5 h-3.5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="isModalOpen" @close="isModalOpen = false" max-width="2xl">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-black text-[var(--text-heading)]">{{ editingPlano ? 'Editar Plano' : 'Novo Plano' }}</h2>
                    <button @click="isModalOpen = false"><XMarkIcon class="w-6 h-6 text-slate-400" /></button>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Nome do Plano" class="text-[10px] font-black uppercase tracking-widest" />
                            <TextInput v-model="form.nome" type="text" class="w-full mt-1" required />
                        </div>
                        <div>
                            <InputLabel value="Tipo" class="text-[10px] font-black uppercase tracking-widest" />
                            <select v-model="form.tipo" class="block w-full mt-1 rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] focus:ring-primary-dynamic focus:border-primary-dynamic text-sm" required>
                                <option value="" disabled>Selecione...</option>
                                <option v-for="(lbl, k) in tipos" :key="k" :value="k">{{ lbl }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Valor de Adesão (R$)" class="text-[10px] font-black uppercase tracking-widest" />
                            <TextInput v-model="form.valor_adesao" type="number" step="0.01" class="w-full mt-1" required />
                        </div>
                        <div>
                            <InputLabel value="Valor Mensalidade (R$)" class="text-[10px] font-black uppercase tracking-widest" />
                            <TextInput v-model="form.valor_mensalidade" type="number" step="0.01" class="w-full mt-1" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Mínimo de Membros" class="text-[10px] font-black uppercase tracking-widest" />
                            <TextInput v-model="form.min_membros" type="number" class="w-full mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Máx. Dependentes" class="text-[10px] font-black uppercase tracking-widest" />
                            <TextInput v-model="form.max_dependentes" type="number" class="w-full mt-1" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Descrição Curta" class="text-[10px] font-black uppercase tracking-widest" />
                        <textarea v-model="form.descricao" rows="2" class="w-full mt-1 rounded-xl border-[var(--border-color)] bg-[var(--bg-main)]"></textarea>
                    </div>

                    <div class="border-t border-[var(--border-color)] pt-4">
                        <InputLabel value="Benefícios Inclusos" class="text-[10px] font-black uppercase tracking-widest mb-2" />
                        <div class="flex gap-2 mb-3">
                            <TextInput v-model="newBeneficio" @keyup.enter.prevent="addBeneficio" placeholder="Ex: Consultas com 50% de desconto" class="flex-grow text-sm" />
                            <SecondaryButton @click="addBeneficio" type="button">Adicionar</SecondaryButton>
                        </div>
                        <ul class="space-y-2">
                            <li v-for="(b, idx) in form.beneficios" :key="idx" class="flex justify-between items-center bg-slate-50 dark:bg-slate-800 p-2 rounded-lg text-sm">
                                <span class="font-medium">{{ b }}</span>
                                <button type="button" @click="removeBeneficio(idx)" class="text-rose-500 hover:text-rose-700 p-1"><XMarkIcon class="w-4 h-4" /></button>
                            </li>
                        </ul>
                    </div>

                    <div class="flex items-center gap-2 mt-4">
                        <input type="checkbox" id="ativoPlano" v-model="form.ativo" class="rounded border-slate-300 text-primary-dynamic">
                        <label for="ativoPlano" class="text-sm font-bold">Plano Ativo</label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="isModalOpen = false" type="button">Cancelar</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">Salvar</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Prices Modal -->
        <Modal :show="isPricesModalOpen" @close="isPricesModalOpen = false" max-width="4xl">
            <div class="p-8">
                <div class="flex justify-between items-center mb-6 border-b border-[var(--border-color)] pb-4">
                    <div>
                        <h2 class="text-xl font-black text-[var(--text-heading)]">Gerenciar Preços de Serviços</h2>
                        <p class="text-sm text-slate-500 mt-1">Plano: {{ currentPlanoForPrices?.nome }}</p>
                    </div>
                    <button @click="isPricesModalOpen = false"><XMarkIcon class="w-6 h-6 text-slate-400" /></button>
                </div>
                
                <div class="overflow-x-auto max-h-[60vh] overflow-y-auto mb-6">
                    <table class="zynix-table w-full text-left">
                        <thead>
                            <tr>
                                <th class="p-3 text-[9px] font-black uppercase tracking-widest text-slate-400">Serviço</th>
                                <th class="p-3 text-[9px] font-black uppercase tracking-widest text-slate-400">Valor Base</th>
                                <th class="p-3 text-[9px] font-black uppercase tracking-widest text-slate-400">Incluso?</th>
                                <th class="p-3 text-[9px] font-black uppercase tracking-widest text-slate-400">Desconto %</th>
                                <th class="p-3 text-[9px] font-black uppercase tracking-widest text-slate-400">Valor Final (R$)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="s in servicosPrices" :key="s.id" class="border-b border-[var(--border-color)]">
                                <td class="p-3 font-bold text-sm">{{ s.nome }}</td>
                                <td class="p-3 text-sm text-slate-500">R$ {{ s.valor_base }}</td>
                                <td class="p-3">
                                    <input type="checkbox" v-model="s.incluso" class="rounded border-slate-300 text-primary-dynamic">
                                </td>
                                <td class="p-3">
                                    <TextInput type="number" v-model="s.desconto_percentual" class="w-24 text-sm p-1" :disabled="s.incluso" />
                                </td>
                                <td class="p-3">
                                    <TextInput type="number" v-model="s.valor_personalizado" class="w-24 text-sm p-1" :disabled="s.incluso" />
                                </td>
                            </tr>
                            <tr v-if="servicosPrices.length === 0">
                                <td colspan="5" class="p-6 text-center text-slate-400 text-xs font-bold uppercase tracking-widest">
                                    Nenhum serviço encontrado. (Simulação de tela)
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <SecondaryButton @click="isPricesModalOpen = false">Cancelar</SecondaryButton>
                    <PrimaryButton @click="savePrices">Salvar Tabela de Preços</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="isDeleteModalOpen" @close="isDeleteModalOpen = false" max-width="md">
            <div class="p-6 text-center">
                <ShieldCheckIcon class="w-12 h-12 text-rose-500 mx-auto mb-4" />
                <h2 class="text-xl font-black mb-2">Atenção!</h2>
                <p class="text-sm text-slate-500 mb-6">
                    Você deseja excluir o plano <strong>{{ planoToDelete?.nome }}</strong>? 
                    <br>Nota: Só é possível excluir se não houver membros ativos vinculados.
                </p>
                <div class="flex justify-center gap-3">
                    <SecondaryButton @click="isDeleteModalOpen = false">Cancelar</SecondaryButton>
                    <DangerButton @click="deletePlano">Excluir Plano</DangerButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
