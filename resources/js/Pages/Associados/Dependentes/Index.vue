<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { 
    PlusIcon, 
    UserIcon, 
    UserGroupIcon, 
    PencilSquareIcon, 
    TrashIcon,
    XMarkIcon,
    ChevronDownIcon,
    CheckBadgeIcon,
    CheckIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    dependentes: Object,
    associados: Array,
});

const isModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isEditing = ref(false);
const dependenteId = ref(null);

const form = useForm({
    associado_id: '',
    nome: '',
    parentesco: '',
    status: 'ativo',
});

function openModal(dependente = null) {
    if (dependente) {
        isEditing.value = true;
        dependenteId.value = dependente.id;
        form.associado_id = dependente.associado_id;
        form.nome = dependente.nome;
        form.parentesco = dependente.parentesco;
        form.status = dependente.status;
    } else {
        isEditing.value = false;
        dependenteId.value = null;
        form.reset();
    }
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
}

function submitForm() {
    if (isEditing.value) {
        form.put(route('associados.dependentes.update', dependenteId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('associados.dependentes.store'), {
            onSuccess: () => closeModal(),
        });
    }
}

// Modal Deleção
function confirmDelete(id) {
    dependenteId.value = id;
    isDeleteModalOpen.value = true;
}

function deleteDependente() {
    router.delete(route('associados.dependentes.destroy', dependenteId.value), {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
        },
    });
}
</script>

<template>
    <Head title="Dependentes" />

    <AuthenticatedLayout>
        <div class="py-6 sm:py-8 lg:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 lg:mb-12">
                    <div>
                        <Breadcrumbs :items="[{ label: 'Associados', href: '/associados' }, { label: 'Dependentes' }]" />
                        <h1 class="text-2xl lg:text-3xl font-black text-[var(--text-heading)] tracking-tighter mt-2">
                            Dependentes da Base
                        </h1>
                    </div>
                    <div>
                        <PrimaryButton @click="openModal()" class="shadow-xl">
                            <PlusIcon class="w-4 h-4 mr-2" />
                            Novo Dependente
                        </PrimaryButton>
                    </div>
                </div>

                <div v-if="$page.props.flash?.success" class="mb-8 p-4 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20 rounded-2xl font-bold flex items-center gap-3 animate-in fade-in slide-in-from-top-4 duration-500">
                    <CheckBadgeIcon class="w-5 h-5" />
                    {{ $page.props.flash.success }}
                </div>

                <div class="zynix-card overflow-hidden !p-0">
                    <div class="p-6 border-b border-[var(--border-color)]">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-primary-dynamic/10 text-primary-dynamic rounded-xl shadow-inner">
                                <UserGroupIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="font-black text-[var(--text-heading)] leading-tight tracking-tight">Vínculos Familiares</h3>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">Gestão de dependentes e beneficiários secundários</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="zynix-table w-full">
                            <thead>
                                <tr>
                                    <th class="pl-8">Associado Titular</th>
                                    <th>Nome do Dependente</th>
                                    <th class="text-center">Parentesco</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-right pr-8">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="dep in dependentes.data" :key="dep.id">
                                    <td class="pl-8">
                                        <div class="flex items-center">
                                            <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mr-3">
                                                <UserIcon class="w-4 h-4" />
                                            </div>
                                            <div class="font-bold text-slate-500 dark:text-slate-400 truncate max-w-[150px]">
                                                {{ dep.associado?.nome || 'N/A' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-black text-[var(--text-heading)] tracking-tight">
                                            {{ dep.nome }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="text-[10px] font-bold text-slate-500 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-lg inline-block uppercase tracking-wider">
                                            {{ dep.parentesco }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span 
                                            :class="dep.status === 'ativo' ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10 border-emerald-100 dark:border-emerald-500/20' : 'text-slate-400 bg-slate-50 dark:bg-slate-500/10 border-slate-100 dark:border-slate-500/20'"
                                            class="px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border inline-flex items-center gap-1.5"
                                        >
                                            <span 
                                                class="w-1.5 h-1.5 rounded-full shadow-sm"
                                                :class="dep.status === 'ativo' ? 'bg-emerald-500' : 'bg-slate-400'"
                                            ></span>
                                            {{ dep.status }}
                                        </span>
                                    </td>
                                    <td class="text-right pr-8">
                                        <div class="flex items-center justify-end space-x-1">
                                            <button @click="openModal(dep)" class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-xl transition-all" title="Editar">
                                                <PencilSquareIcon class="w-4 h-4" />
                                            </button>
                                            <button @click="confirmDelete(dep.id)" class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-all" title="Excluir">
                                                <TrashIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="dependentes.data.length === 0">
                                    <td colspan="5" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="p-4 rounded-2xl bg-primary-dynamic/10 text-primary-dynamic mb-4">
                                                <UserGroupIcon class="w-8 h-8" />
                                            </div>
                                            <p class="font-black text-slate-400 uppercase tracking-widest text-xs">Nenhum dependente cadastrado</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="p-6 border-t border-[var(--border-color)] flex flex-col sm:flex-row items-center justify-between gap-4" v-if="dependentes.data.length > 0">
                         <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                             Mostrando {{ dependentes.from }}-{{ dependentes.to }} de {{ dependentes.total }}
                         </div>
                         <div class="flex items-center gap-1 flex-wrap justify-center">
                             <template v-for="(link, p) in dependentes.links" :key="p">
                                 <div v-if="link.url === null" class="px-3 py-1.5 text-[10px] font-black text-slate-300 uppercase tracking-widest border border-[var(--border-color)] rounded-lg cursor-not-allowed" v-html="link.label" />
                                 <Link v-else 
                                    :href="link.url"
                                    class="px-3 py-1.5 text-[10px] font-black uppercase tracking-widest border transition-all rounded-lg"
                                    :class="link.active ? 'bg-primary-dynamic text-white border-primary-dynamic shadow-lg shadow-primary-dynamic/20' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 border-[var(--border-color)]'"
                                    v-html="link.label" 
                                 />
                             </template>
                         </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="isModalOpen" @close="closeModal" max-width="md">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <p class="text-[10px] font-black text-primary-dynamic uppercase tracking-[0.2em] mb-1">Módulo de Cadastro</p>
                        <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tighter">
                            {{ isEditing ? 'Editar Dependente' : 'Novo Dependente' }}
                        </h2>
                    </div>
                    <button @click="closeModal" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-8">
                    <div class="space-y-6">
                        <!-- Associado Selector -->
                        <div>
                            <InputLabel for="associado_id" value="Associado Titular" />
                            <div class="relative">
                                <select id="associado_id" v-model="form.associado_id" 
                                    class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic py-3 text-sm font-medium pr-10 appearance-none" required>
                                    <option value="" disabled>Selecione um associado</option>
                                    <option v-for="ass in associados" :key="ass.id" :value="ass.id">
                                        {{ ass.nome }}
                                    </option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                    <ChevronDownIcon class="w-4 h-4" />
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.associado_id" />
                        </div>

                        <!-- Nome -->
                        <div>
                            <InputLabel for="nome" value="Nome Completo" />
                            <TextInput id="nome" v-model="form.nome" type="text" class="block w-full bg-[var(--bg-main)]" placeholder="Nome do dependente" required />
                            <InputError class="mt-2" :message="form.errors.nome" />
                        </div>

                        <!-- Parentesco & Status Row -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="parentesco" value="Parentesco" />
                                <div class="relative">
                                    <select id="parentesco" v-model="form.parentesco" 
                                        class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic py-3 text-sm font-medium pr-10 appearance-none">
                                        <option value="Cônjuge">Cônjuge</option>
                                        <option value="Filho(a)">Filho(a)</option>
                                        <option value="Enteado(a)">Enteado(a)</option>
                                        <option value="Pai/Mãe">Pai/Mãe</option>
                                        <option value="Outro">Outro</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                        <ChevronDownIcon class="w-4 h-4" />
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors.parentesco" />
                            </div>
                            <div>
                                <InputLabel for="status" value="Status" />
                                <div class="relative">
                                    <select id="status" v-model="form.status" 
                                        class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic py-3 text-sm font-medium pr-10 appearance-none">
                                        <option value="ativo">Ativo</option>
                                        <option value="inativo">Inativo</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                        <ChevronDownIcon class="w-4 h-4" />
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors.status" />
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 flex items-center justify-end gap-3 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="closeModal" class="!rounded-full !py-4 !px-8 text-slate-600">
                            <XMarkIcon class="w-4 h-4 mr-2" /> Cancelar
                        </SecondaryButton>
                        <PrimaryButton type="submit" class="!rounded-full !py-4 !px-8 shadow-xl shadow-primary-dynamic/20" :disabled="form.processing">
                            <CheckIcon v-if="isEditing" class="w-4 h-4 mr-2" />
                            <PlusIcon v-else class="w-4 h-4 mr-2" />
                            {{ isEditing ? 'Atualizar' : 'Salvar Dependente' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="isDeleteModalOpen" @close="isDeleteModalOpen = false" max-width="md">
            <div class="p-8">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-6 bg-rose-50 dark:bg-rose-500/10 rounded-full text-rose-500 shadow-inner">
                    <TrashIcon class="w-8 h-8" />
                </div>
                <h2 class="text-xl font-black text-[var(--text-heading)] text-center tracking-tighter">Confirmar Exclusão</h2>
                <p class="mt-3 text-sm text-slate-500 dark:text-slate-400 text-center font-medium leading-relaxed italic">Tem certeza que deseja excluir este dependente? Esta ação é irreversível e removerá todos os vínculos no sistema.</p>
                <div class="mt-8 flex justify-center gap-3 border-t border-[var(--border-color)] pt-8">
                    <SecondaryButton @click="isDeleteModalOpen = false" class="!rounded-full !py-3 !px-6">Manter Cadastro</SecondaryButton>
                    <DangerButton @click="deleteDependente" class="!rounded-full !py-3 !px-6 shadow-xl shadow-rose-500/20">
                        <TrashIcon class="w-4 h-4 mr-2" /> Excluir Agora
                    </DangerButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
