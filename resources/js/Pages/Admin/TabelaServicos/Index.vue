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
    TableCellsIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    servicos: Object,
    categorias: Object,
    filters: Object,
});

const isModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const editingService = ref(null);
const serviceToDelete = ref(null);

const form = useForm({
    codigo: '',
    nome: '',
    categoria: '',
    descricao: '',
    valor_base: 0,
    ativo: true,
    ordem: 0,
});

const openCreateModal = () => {
    editingService.value = null;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (servico) => {
    editingService.value = servico;
    form.codigo = servico.codigo;
    form.nome = servico.nome;
    form.categoria = servico.categoria;
    form.descricao = servico.descricao || '';
    form.valor_base = servico.valor_base;
    form.ativo = servico.ativo;
    form.ordem = servico.ordem || 0;
    isModalOpen.value = true;
};

const confirmDelete = (servico) => {
    serviceToDelete.value = servico;
    isDeleteModalOpen.value = true;
};

const submit = () => {
    if (editingService.value) {
        form.put(route('admin.tabela-servicos.update', editingService.value.id), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('admin.tabela-servicos.store'), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    }
};

const deleteService = () => {
    if (serviceToDelete.value) {
        router.delete(route('admin.tabela-servicos.destroy', serviceToDelete.value.id), {
            onSuccess: () => {
                isDeleteModalOpen.value = false;
                serviceToDelete.value = null;
            }
        });
    }
};

const toggleStatus = (id) => {
    router.post(route('admin.tabela-servicos.toggle', id), {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="Tabela de Serviços" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <Breadcrumbs :items="[{ label: 'Admin' }, { label: 'Tabela de Serviços' }]" />
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Tabela de Serviços</h2>
                    <p class="text-sm text-[var(--text-muted)] mt-0.5">Gerencie os serviços, consultas e procedimentos oferecidos.</p>
                </div>
                <div>
                    <PrimaryButton @click="openCreateModal" class="shadow-xl">
                        <PlusIcon class="w-4 h-4 mr-2" />
                        Novo Serviço
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="pb-12">
            <div class="zynix-card overflow-hidden !p-0">
                <div class="p-6 border-b border-[var(--border-color)] flex justify-between items-center bg-[var(--bg-surface)]">
                    <h3 class="text-sm font-black text-[var(--text-heading)] uppercase tracking-widest">Catálogo de Serviços</h3>
                    <div>
                        <!-- Simples filtro de categoria (exemplo UI) -->
                        <select class="rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-sm focus:ring-primary-dynamic focus:border-primary-dynamic">
                            <option value="">Todas as Categorias</option>
                            <option v-for="(label, key) in categorias" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </div>
                </div>

                <div v-if="!servicos.data || servicos.data.length === 0" class="p-12 text-center">
                    <TableCellsIcon class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Nenhum serviço cadastrado.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="zynix-table w-full text-left">
                        <thead>
                            <tr>
                                <th class="p-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Código</th>
                                <th class="p-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Serviço</th>
                                <th class="p-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Categoria</th>
                                <th class="p-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Valor Base</th>
                                <th class="p-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Status</th>
                                <th class="p-4 text-[9px] font-black uppercase tracking-widest text-slate-400 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="s in servicos.data" :key="s.id" class="border-b border-[var(--border-color)] hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="p-4 font-bold text-xs text-slate-500">{{ s.codigo }}</td>
                                <td class="p-4 font-black text-sm text-[var(--text-heading)]">{{ s.nome }}</td>
                                <td class="p-4">
                                    <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-bold uppercase tracking-widest rounded-md">
                                        {{ categorias[s.categoria] || s.categoria }}
                                    </span>
                                </td>
                                <td class="p-4 font-bold text-emerald-600">
                                    {{ new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(s.valor_base) }}
                                </td>
                                <td class="p-4">
                                    <button @click="toggleStatus(s.id)" class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest rounded-md transition-all"
                                        :class="s.ativo ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-500/10' : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-800'">
                                        {{ s.ativo ? 'Ativo' : 'Inativo' }}
                                    </button>
                                </td>
                                <td class="p-4 flex gap-2 justify-end">
                                    <button @click="openEditModal(s)" class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-xl transition-all" title="Editar">
                                        <PencilSquareIcon class="w-4 h-4" />
                                    </button>
                                    <button @click="confirmDelete(s)" class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-all" title="Excluir">
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination (Example placeholder for links) -->
                <div v-if="servicos.links && servicos.links.length > 3" class="p-4 border-t border-[var(--border-color)] flex justify-center">
                    <div class="flex gap-1">
                        <!-- Implement inertia links for pagination as per project structure -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="isModalOpen" @close="isModalOpen = false" max-width="lg">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <p class="text-[10px] font-black text-primary-dynamic uppercase tracking-[0.2em] mb-1">Catálogo</p>
                        <h2 class="text-xl font-black text-[var(--text-heading)] tracking-tighter">
                            {{ editingService ? 'Editar Serviço' : 'Novo Serviço' }}
                        </h2>
                    </div>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="codigo" value="Código" class="text-[10px] font-black uppercase tracking-widest" />
                            <TextInput id="codigo" v-model="form.codigo" type="text" class="block w-full mt-1" required />
                            <InputError :message="form.errors.codigo" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="categoria" value="Categoria" class="text-[10px] font-black uppercase tracking-widest" />
                            <select id="categoria" v-model="form.categoria" class="block w-full mt-1 rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] focus:ring-primary-dynamic focus:border-primary-dynamic text-sm" required>
                                <option value="" disabled>Selecione...</option>
                                <option v-for="(label, key) in categorias" :key="key" :value="key">{{ label }}</option>
                            </select>
                            <InputError :message="form.errors.categoria" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="nome" value="Nome do Serviço" class="text-[10px] font-black uppercase tracking-widest" />
                        <TextInput id="nome" v-model="form.nome" type="text" class="block w-full mt-1" required />
                        <InputError :message="form.errors.nome" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="descricao" value="Descrição" class="text-[10px] font-black uppercase tracking-widest" />
                        <textarea id="descricao" v-model="form.descricao" rows="3" class="block w-full mt-1 rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] focus:ring-primary-dynamic focus:border-primary-dynamic text-sm"></textarea>
                        <InputError :message="form.errors.descricao" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="valor_base" value="Valor Base (R$)" class="text-[10px] font-black uppercase tracking-widest" />
                            <TextInput id="valor_base" v-model="form.valor_base" type="number" step="0.01" class="block w-full mt-1" required />
                            <InputError :message="form.errors.valor_base" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="ordem" value="Ordem de Exibição" class="text-[10px] font-black uppercase tracking-widest" />
                            <TextInput id="ordem" v-model="form.ordem" type="number" class="block w-full mt-1" />
                        </div>
                    </div>

                    <div class="flex items-center gap-2 mt-4">
                        <input type="checkbox" id="ativo" v-model="form.ativo" class="rounded border-slate-300 text-primary-dynamic shadow-sm focus:ring-primary-dynamic">
                        <label for="ativo" class="text-sm font-bold text-[var(--text-heading)]">Serviço Ativo</label>
                    </div>

                    <div class="pt-6 flex justify-end gap-3 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="isModalOpen = false" type="button">Cancelar</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            {{ editingService ? 'Atualizar' : 'Salvar' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="isDeleteModalOpen" @close="isDeleteModalOpen = false" max-width="md">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 bg-rose-50 text-rose-500 rounded-full dark:bg-rose-500/10">
                        <ExclamationTriangleIcon class="w-6 h-6" />
                    </div>
                    <h2 class="text-xl font-black text-[var(--text-heading)]">Excluir Serviço</h2>
                </div>
                <p class="text-sm text-slate-500 mb-6">
                    Tem certeza que deseja excluir o serviço <strong>{{ serviceToDelete?.nome }}</strong>? Esta ação não poderá ser desfeita.
                </p>
                <div class="flex justify-end gap-3 pt-6 border-t border-[var(--border-color)]">
                    <SecondaryButton @click="isDeleteModalOpen = false">Cancelar</SecondaryButton>
                    <DangerButton @click="deleteService">Excluir</DangerButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
