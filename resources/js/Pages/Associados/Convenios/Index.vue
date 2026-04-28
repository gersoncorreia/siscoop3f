<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { 
    PlusIcon, 
    PencilSquareIcon as PencilIcon, 
    TrashIcon, 
    CheckCircleIcon, 
    XCircleIcon,
    ExclamationCircleIcon,
    BuildingOffice2Icon,
    XMarkIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    convenios: Array
});

// Modal states
const isModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isEditing = ref(false);
const currentId = ref(null);

const form = useForm({
    nome_empresa: '',
    ramo: '',
    desconto: '',
    contato: '',
    descricao: '',
    ativo: true,
});

function openModal(convenioEdit = null) {
    if (convenioEdit) {
        isEditing.value = true;
        currentId.value = convenioEdit.id;
        form.nome_empresa = convenioEdit.nome_empresa;
        form.ramo = convenioEdit.ramo || '';
        form.desconto = convenioEdit.desconto || '';
        form.contato = convenioEdit.contato || '';
        form.descricao = convenioEdit.descricao || '';
        form.ativo = !!convenioEdit.ativo;
    } else {
        isEditing.value = false;
        currentId.value = null;
        form.reset();
        form.ativo = true;
    }
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
}

function submit() {
    if (isEditing.value) {
        form.put(route('associados.convenios.update', currentId.value), {
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('associados.convenios.store'), {
            onSuccess: () => closeModal()
        });
    }
}

function confirmDelete(id) {
    currentId.value = id;
    isDeleteModalOpen.value = true;
}

function deleteConvenio() {
    form.delete(route('associados.convenios.destroy', currentId.value), {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
        }
    });
}
</script>

<template>
    <Head title="Convênios e Parcerias" />

    <AuthenticatedLayout>
        <div class="py-6 sm:py-8 lg:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 lg:mb-12">
                    <div>
                        <Breadcrumbs :items="[{ label: 'Associados', href: '/associados' }, { label: 'Convênios' }]" />
                        <h1 class="text-2xl lg:text-3xl font-black text-[var(--text-heading)] tracking-tighter mt-2">
                            Convênios e Parcerias
                        </h1>
                    </div>
                    <div>
                        <PrimaryButton @click="openModal()" class="shadow-xl">
                            <PlusIcon class="w-4 h-4 mr-2" />
                            Novo Convênio
                        </PrimaryButton>
                    </div>
                </div>
                
                <div v-if="$page.props.flash?.success" class="mb-8 p-4 bg-emerald-500/10 text-emerald-500 rounded-xl border border-emerald-500/20 flex items-center">
                    <CheckCircleIcon class="w-5 h-5 mr-2" />
                    <span class="text-xs font-black uppercase tracking-widest">{{ $page.props.flash.success }}</span>
                </div>

                <div class="zynix-card overflow-hidden !p-0">
                    <div class="overflow-x-auto">
                        <table class="zynix-table w-full">
                            <thead>
                                <tr>
                                    <th class="pl-8">Empresa</th>
                                    <th class="text-center">Ramo</th>
                                    <th class="text-center">Desconto</th>
                                    <th class="text-center">Contato</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-right pr-8">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="convenio in convenios" :key="convenio.id">
                                    <td class="pl-8">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-9 h-9 p-2 rounded-xl bg-indigo-500/10 text-indigo-500 shadow-inner">
                                                <BuildingOffice2Icon class="w-full h-full" />
                                            </div>
                                            <div class="ml-4">
                                                <div class="font-black text-[var(--text-heading)] tracking-tight">{{ convenio.nome_empresa }}</div>
                                                <div v-if="convenio.descricao" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-tight truncate max-w-xs">{{ convenio.descricao }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="px-2 py-1 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-lg text-[10px] font-black uppercase tracking-widest border border-indigo-100 dark:border-indigo-500/20">
                                            {{ convenio.ramo || 'Geral' }}
                                        </span>
                                    </td>
                                    <td class="text-center font-black text-[var(--text-heading)] tracking-tight">
                                        {{ convenio.desconto || '-' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                            {{ convenio.contato || 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span 
                                            :class="convenio.ativo ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-500/20' : 'bg-slate-50 dark:bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-100 dark:border-slate-500/20'"
                                            class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider border rounded-lg inline-flex items-center"
                                        >
                                            <component :is="convenio.ativo ? CheckCircleIcon : XCircleIcon" class="w-3 h-3 mr-1" />
                                            {{ convenio.ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                    <td class="text-right pr-8 whitespace-nowrap">
                                        <div class="flex items-center justify-end space-x-1">
                                            <button 
                                                @click="openModal(convenio)" 
                                                class="p-2 text-slate-400 hover:text-primary-dynamic transition-all rounded-full hover:bg-primary-dynamic/10"
                                                title="Editar"
                                            >
                                                <PencilIcon class="w-4 h-4" />
                                            </button>
                                            <button 
                                                @click="confirmDelete(convenio.id)" 
                                                class="p-2 text-slate-400 hover:text-rose-500 transition-all rounded-full hover:bg-rose-500/10"
                                                title="Excluir"
                                            >
                                                <TrashIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!convenios.length">
                                    <td colspan="6" class="px-6 py-20 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-400 mb-4">
                                                <BuildingOffice2Icon class="w-8 h-8" />
                                            </div>
                                            <p class="font-black text-slate-400 uppercase tracking-widest text-xs">Nenhum convênio cadastrado</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Form Modal -->
                <Modal :show="isModalOpen" @close="closeModal" max-width="lg">
                    <div class="p-8">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <p class="text-[10px] font-black text-primary-dynamic uppercase tracking-[0.2em] mb-1">Convênios</p>
                                <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tighter">
                                    {{ isEditing ? 'Editar Convênio' : 'Cadastrar Novo Convênio' }}
                                </h2>
                            </div>
                            <button @click="closeModal" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                                <XMarkIcon class="w-6 h-6" />
                            </button>
                        </div>
                        
                        <form @submit.prevent="submit" class="space-y-8">
                            <div class="space-y-6">
                                <div>
                                    <InputLabel for="nome_empresa" value="Nome da Empresa / Parceiro *" />
                                    <TextInput id="nome_empresa" v-model="form.nome_empresa" type="text" class="block w-full bg-[var(--bg-main)]" required autofocus placeholder="Ex: Farmácia São João" />
                                    <InputError class="mt-2" :message="form.errors.nome_empresa" />
                                </div>

                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <InputLabel for="ramo" value="Ramo de Atuação" />
                                        <TextInput id="ramo" v-model="form.ramo" type="text" class="block w-full bg-[var(--bg-main)]" placeholder="Ex: Farmácia, Academia..." />
                                        <InputError class="mt-2" :message="form.errors.ramo" />
                                    </div>
                                    <div>
                                        <InputLabel for="desconto" value="Regra de Desconto" />
                                        <TextInput id="desconto" v-model="form.desconto" type="text" class="block w-full bg-[var(--bg-main)]" placeholder="Ex: 20% em perfumaria" />
                                        <InputError class="mt-2" :message="form.errors.desconto" />
                                    </div>
                                </div>
                                
                                <div>
                                    <InputLabel for="contato" value="Contato / Site / Endereço" />
                                    <TextInput id="contato" v-model="form.contato" type="text" class="block w-full bg-[var(--bg-main)]" placeholder="(xx) xxxx-xxxx ou www.site.com" />
                                    <InputError class="mt-2" :message="form.errors.contato" />
                                </div>

                                <div>
                                    <InputLabel for="descricao" value="Descrição e Regras de Uso" />
                                    <textarea id="descricao" v-model="form.descricao" rows="3" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic py-4 px-5 text-sm font-medium transition-all" placeholder="Detalhes de como o associado deve utilizar o benefício..."></textarea>
                                    <InputError class="mt-2" :message="form.errors.descricao" />
                                </div>

                                <div>
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="checkbox" v-model="form.ativo" class="w-5 h-5 rounded border-[var(--border-color)] bg-[var(--bg-main)] text-primary-dynamic focus:ring-primary-dynamic shadow-sm transition-all" />
                                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest group-hover:text-primary-dynamic transition-colors">Convênio Ativo (Visível para associados)</span>
                                    </label>
                                    <InputError class="mt-2" :message="form.errors.ativo" />
                                </div>
                            </div>

                            <div class="pt-8 flex items-center justify-end space-x-3 border-t border-[var(--border-color)]">
                                <SecondaryButton @click="closeModal" class="!rounded-full !py-4 !px-8">Cancelar</SecondaryButton>
                                <PrimaryButton type="submit" class="!rounded-full !py-4 !px-8 shadow-xl shadow-primary-dynamic/20" :disabled="form.processing">
                                    {{ isEditing ? 'Salvar Configurações' : 'Cadastrar Convênio' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </Modal>

                <!-- Delete Confirmation Modal -->
                <Modal :show="isDeleteModalOpen" @close="isDeleteModalOpen = false" max-width="md">
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <ExclamationCircleIcon class="w-10 h-10 text-red-500" />
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 mb-2">Excluir Convênio?</h2>
                        <p class="text-slate-500 text-sm mb-6">
                            Tem certeza que deseja remover esta parceria? Esta ação não poderá ser desfeita.
                        </p>
                        
                        <div class="flex justify-center space-x-3">
                            <SecondaryButton @click="isDeleteModalOpen = false">Cancelar</SecondaryButton>
                            <DangerButton @click="deleteConvenio" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Sim, excluir parceria
                            </DangerButton>
                        </div>
                    </div>
                </Modal>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
