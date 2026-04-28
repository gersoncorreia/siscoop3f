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
    BuildingOffice2Icon,
    MapPinIcon,
    TagIcon,
    PencilSquareIcon,
    TrashIcon,
    XMarkIcon,
    UserIcon,
    BuildingLibraryIcon,
    MapIcon,
    CheckIcon
} from '@heroicons/vue/24/outline';
import { ref } from 'vue';

const props = defineProps({
    prestadores: Array,
});

const showModal = ref(false);
const editingPrestador = ref(null);

const form = useForm({
    tipo: 'clinica',
    nome: '',
    especialidades: [],
    endereco: '',
});

const openCreateModal = () => {
    editingPrestador.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (prestador) => {
    editingPrestador.value = prestador;
    form.tipo = prestador.tipo;
    form.nome = prestador.nome;
    form.especialidades = prestador.especialidades || [];
    form.endereco = prestador.endereco;
    showModal.value = true;
};

const submit = () => {
    if (editingPrestador.value) {
        form.put(route('saude.prestadores.update', editingPrestador.value.id), {
            onSuccess: () => { showModal.value = false; form.reset(); }
        });
    } else {
        form.post(route('saude.prestadores.store'), {
            onSuccess: () => { showModal.value = false; form.reset(); }
        });
    }
};

const deletePrestador = (id) => {
    if (confirm('Excluir este prestador?')) {
        form.delete(route('saude.prestadores.destroy', id));
    }
};
</script>

<template>
    <Head title="Rede Credenciada" />

    <AuthenticatedLayout>
        <div class="py-6 sm:py-8 lg:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 lg:mb-12">
                    <div>
                        <Breadcrumbs :items="[{ label: 'Saúde' }, { label: 'Rede Credenciada' }]" />
                        <h1 class="text-2xl lg:text-3xl font-black text-[var(--text-heading)] tracking-tighter mt-2">
                            Rede Credenciada
                        </h1>
                    </div>
                    <div>
                        <PrimaryButton @click="openCreateModal" class="shadow-xl">
                            <PlusIcon class="w-4 h-4 mr-2" />
                            Novo Prestador
                        </PrimaryButton>
                    </div>
                </div>

                <!-- Provider List -->
                <div class="zynix-table-wrapper">
                    <div class="overflow-x-auto">
                        <table class="zynix-table w-full">
                            <thead>
                                <tr>
                                    <th class="pl-8">Prestador</th>
                                    <th>Tipo</th>
                                    <th>Especialidades</th>
                                    <th>Endereço</th>
                                    <th class="text-right pr-8">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="p in prestadores" :key="p.id">
                                    <td class="pl-8">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-9 h-9 p-2 rounded-xl bg-primary-dynamic/10 text-primary-dynamic shadow-inner">
                                                <BuildingOffice2Icon class="w-full h-full" />
                                            </div>
                                            <div class="ml-4">
                                                <div class="font-black text-[var(--text-heading)] tracking-tight">{{ p.nome }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded text-[9px] font-black uppercase tracking-widest border border-slate-200/50 dark:border-slate-700/50">
                                                {{ p.tipo }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex flex-wrap gap-1">
                                            <span v-for="esp in p.especialidades" :key="esp" class="px-2 py-0.5 bg-primary-dynamic/5 text-primary-dynamic rounded text-[9px] font-black uppercase tracking-widest border border-primary-dynamic/10">
                                                {{ esp }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="max-w-xs flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 italic">
                                            <MapPinIcon class="w-3 h-3 flex-shrink-0 opacity-50" />
                                            <span class="truncate">{{ p.endereco }}</span>
                                        </div>
                                    </td>
                                    <td class="text-right pr-8">
                                        <div class="flex justify-end items-center space-x-1">
                                            <button @click="openEditModal(p)" class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-xl transition-all" title="Editar">
                                                <PencilSquareIcon class="w-4 h-4" />
                                            </button>
                                            <button @click="deletePrestador(p.id)" class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-all" title="Excluir">
                                                <TrashIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="prestadores.length === 0">
                                    <td colspan="5" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="p-4 rounded-2xl bg-primary-dynamic/10 text-primary-dynamic mb-4">
                                                <BuildingOffice2Icon class="w-8 h-8" />
                                            </div>
                                            <p class="font-black text-slate-400 uppercase tracking-widest text-xs">Nenhum prestador credenciado</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showModal" @close="showModal = false" max-width="lg">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <p class="text-[10px] font-black text-primary-dynamic uppercase tracking-[0.2em] mb-1">Rede de Saúde</p>
                        <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tighter">
                            {{ editingPrestador ? 'Editar Prestador' : 'Novo Prestador' }}
                        </h2>
                    </div>
                    <button @click="showModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <div class="space-y-6">
                        <div>
                            <InputLabel for="nome" value="Nome / Razão Social *" />
                            <TextInput id="nome" v-model="form.nome" type="text" class="block w-full" placeholder="Ex: Hospital Central" required autofocus />
                            <InputError class="mt-2" :message="form.errors.nome" />
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <InputLabel value="Tipo *" />
                                <select v-model="form.tipo" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic py-3 text-sm font-medium" required>
                                    <option value="clinica">Clínica</option>
                                    <option value="hospital">Hospital</option>
                                    <option value="medico_pf">Médico (PF)</option>
                                    <option value="laboratorio">Laboratório</option>
                                </select>
                            </div>
                            <div>
                                <InputLabel value="Especialidades" />
                                <TextInput 
                                    :value="form.especialidades.join(', ')" 
                                    @input="e => form.especialidades = e.target.value.split(',').map(s => s.trim())"
                                    type="text" 
                                    class="block w-full" 
                                    placeholder="Ex: Geral, Cardio..." 
                                />
                            </div>
                        </div>

                        <div>
                            <InputLabel value="Endereço Completo" />
                            <textarea v-model="form.endereco" rows="3" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic py-4 px-5 text-sm font-medium transition-all" placeholder="Ex: Av. Brasil, 123 - Centro"></textarea>
                        </div>
                    </div>

                    <div class="pt-8 flex items-center justify-end space-x-3 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="showModal = false" type="button" class="!rounded-full !py-4 !px-8 text-slate-600">
                            <XMarkIcon class="w-4 h-4 mr-2" /> Cancelar
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing" class="!rounded-full !py-4 !px-8 shadow-xl shadow-primary-dynamic/20">
                            <CheckIcon v-if="editingPrestador" class="w-4 h-4 mr-2" />
                            <PlusIcon v-else class="w-4 h-4 mr-2" />
                            {{ editingPrestador ? 'Salvar Dados' : 'Efetuar Credenciamento' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
