<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    DocumentTextIcon, 
    PlusIcon, 
    TrashIcon, 
    PencilIcon, 
    CheckCircleIcon,
    ExclamationTriangleIcon,
    XMarkIcon,
    CheckIcon,
    BuildingOffice2Icon
} from '@heroicons/vue/24/outline';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    contratos: Array,
    tenants: Array,
});

const isModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isEditing = ref(false);
const currentContractId = ref(null);

const form = useForm({
    tenant_contratante_id: '',
    tenant_prestador_id: '',
    status: 'pendente',
    data_inicio: '',
    data_fim: '',
    descricao: '',
});

const openModal = (contract = null) => {
    if (contract) {
        isEditing.value = true;
        currentContractId.value = contract.id;
        form.tenant_contratante_id = contract.tenant_contratante_id;
        form.tenant_prestador_id = contract.tenant_prestador_id;
        form.status = contract.status;
        form.data_inicio = contract.data_inicio ? contract.data_inicio.split('T')[0] : '';
        form.data_fim = contract.data_fim ? contract.data_fim.split('T')[0] : '';
        form.descricao = contract.descricao || '';
    } else {
        isEditing.value = false;
        currentContractId.value = null;
        form.reset();
        // Set defaults
        form.status = 'pendente';
        form.data_inicio = new Date().toISOString().split('T')[0];
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('master-admin.contratos.update', currentContractId.value), {
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('master-admin.contratos.store'), {
            onSuccess: () => closeModal()
        });
    }
};

const confirmDelete = (id) => {
    currentContractId.value = id;
    isDeleteModalOpen.value = true;
};

const deleteContract = () => {
    form.delete(route('master-admin.contratos.destroy', currentContractId.value), {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
        }
    });
};
</script>

<template>
    <Head title="Contratos de Prestação" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <Breadcrumbs :items="[{ label: 'Contratos' }]" />
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">
                        Gestão de Contratos entre Entidades
                    </h2>
                    <p class="text-sm text-[var(--text-muted)] mt-1 font-medium">
                        Vincule fundações a cooperativas para viabilizar atendimento cruzado de serviços e saúde.
                    </p>
                </div>
                <div>
                    <button @click="openModal()" class="btn-primary flex items-center gap-2">
                        <PlusIcon class="w-4 h-4" />
                        Registrar Novo Contrato
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-8 pb-12">
            <!-- Alert success -->
            <div v-if="$page.props.flash?.success" class="zynix-card !border-emerald-200 dark:!border-emerald-500/30 !bg-emerald-50 dark:!bg-emerald-500/10 relative">
                <button @click="$page.props.flash.success = null" class="absolute top-3 right-3 p-1 rounded-full hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-colors">
                    <XMarkIcon class="w-4 h-4 text-emerald-600" />
                </button>
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-emerald-100 dark:bg-emerald-500/20 rounded-xl shrink-0">
                        <CheckCircleIcon class="w-5 h-5 text-emerald-600" />
                    </div>
                    <pre class="text-sm font-bold text-emerald-700 dark:text-emerald-300 whitespace-pre-wrap leading-relaxed">{{ $page.props.flash.success }}</pre>
                </div>
            </div>

            <!-- List Card -->
            <div class="zynix-card overflow-hidden !p-0">
                <div class="p-6 border-b border-[var(--border-color)]">
                    <h3 class="text-lg font-black text-[var(--text-heading)]">Contratos Vigentes</h3>
                    <p class="text-[12px] font-medium text-slate-500">Listagem de acordos operacionais ativos e pendentes.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full zynix-table text-left">
                        <thead>
                            <tr>
                                <th class="p-4 uppercase text-[10px] font-black text-slate-400">Contratante</th>
                                <th class="p-4 uppercase text-[10px] font-black text-slate-400">Prestadora (Fundação)</th>
                                <th class="p-4 uppercase text-[10px] font-black text-slate-400">Status</th>
                                <th class="p-4 uppercase text-[10px] font-black text-slate-400">Vigência</th>
                                <th class="p-4 uppercase text-[10px] font-black text-slate-400 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in contratos" :key="c.id" class="border-b border-[var(--border-color)] group hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="p-4 font-black text-sm text-[var(--text-heading)]">
                                    {{ c.contratante?.nome }}
                                    <span class="block text-[8px] font-bold text-slate-400 uppercase">ID: {{ c.tenant_contratante_id }}</span>
                                </td>
                                <td class="p-4 font-black text-sm text-[var(--text-heading)]">
                                    {{ c.prestador?.nome }}
                                    <span class="block text-[8px] font-bold text-slate-400 uppercase">ID: {{ c.tenant_prestador_id }}</span>
                                </td>
                                <td class="p-4">
                                    <span v-if="c.status === 'ativo'" class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 text-[10px] font-bold uppercase tracking-widest rounded-md">Ativo</span>
                                    <span v-else-if="c.status === 'pendente'" class="px-2.5 py-1 bg-amber-50 dark:bg-amber-500/10 text-amber-600 text-[10px] font-bold uppercase tracking-widest rounded-md">Pendente</span>
                                    <span v-else-if="c.status === 'suspenso'" class="px-2.5 py-1 bg-yellow-50 dark:bg-yellow-500/10 text-yellow-600 text-[10px] font-bold uppercase tracking-widest rounded-md">Suspenso</span>
                                    <span v-else class="px-2.5 py-1 bg-rose-50 dark:bg-rose-500/10 text-rose-600 text-[10px] font-bold uppercase tracking-widest rounded-md">Encerrado</span>
                                </td>
                                <td class="p-4 text-xs font-semibold text-[var(--text-muted)]">
                                    {{ new Date(c.data_inicio).toLocaleDateString('pt-BR') }} 
                                    <span v-if="c.data_fim"> até {{ new Date(c.data_fim).toLocaleDateString('pt-BR') }}</span>
                                    <span v-else class="text-emerald-500 font-bold uppercase text-[9px] block">Sem limite</span>
                                </td>
                                <td class="p-4 flex gap-2 justify-end">
                                    <button @click="openModal(c)" class="p-2 rounded-xl text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-all font-bold" title="Editar Contrato">
                                        <PencilIcon class="w-4 h-4" />
                                    </button>
                                    <button @click="confirmDelete(c.id)" class="p-2 rounded-xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-all font-bold" title="Remover Contrato">
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="contratos.length === 0">
                                <td colspan="5" class="p-8 text-center text-[11px] font-bold uppercase tracking-widest text-slate-400">
                                    Nenhum contrato foi registrado ainda.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Form Modal -->
            <Modal :show="isModalOpen" @close="closeModal">
                <div class="p-6">
                    <h2 class="text-xl font-black text-[var(--text-heading)] flex items-center gap-2 mb-6">
                        <BuildingOffice2Icon class="w-5 h-5 text-primary-dynamic"/>
                        {{ isEditing ? 'Editar Configurações de Contrato' : 'Registrar Novo Acordo Operacional' }}
                    </h2>

                    <form @submit.prevent="submitForm" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="contratante" value="Entidade Contratante" class="!font-black tracking-widest uppercase text-[10px]" />
                                <select id="contratante" class="block w-full border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 rounded-xl text-xs font-bold text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic transition-all py-2.5" v-model="form.tenant_contratante_id" required>
                                    <option value="" disabled>Selecione...</option>
                                    <option v-for="t in tenants" :key="t.id" :value="t.id">{{ t.nome }} ({{ t.tipo === 'fundacao' ? 'Fundação' : 'Cooperativa' }})</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.tenant_contratante_id" />
                            </div>

                            <div>
                                <InputLabel for="prestadora" value="Entidade Prestadora / Parceira" class="!font-black tracking-widest uppercase text-[10px]" />
                                <select id="prestadora" class="block w-full border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 rounded-xl text-xs font-bold text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic transition-all py-2.5" v-model="form.tenant_prestador_id" required>
                                    <option value="" disabled>Selecione...</option>
                                    <option v-for="t in tenants" :key="t.id" :value="t.id">{{ t.nome }} ({{ t.tipo === 'fundacao' ? 'Fundação' : 'Cooperativa' }})</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.tenant_prestador_id" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <InputLabel for="status" value="Status" class="!font-black tracking-widest uppercase text-[10px]" />
                                <select id="status" class="block w-full border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 rounded-xl text-xs font-bold text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic transition-all py-2.5" v-model="form.status" required>
                                    <option value="pendente">Pendente</option>
                                    <option value="ativo">Ativo</option>
                                    <option value="suspenso">Suspenso</option>
                                    <option value="encerrado">Encerrado</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.status" />
                            </div>

                            <div>
                                <InputLabel for="data_inicio" value="Data de Início" class="!font-black tracking-widest uppercase text-[10px]" />
                                <TextInput id="data_inicio" type="date" class="block w-full" v-model="form.data_inicio" required />
                                <InputError class="mt-2" :message="form.errors.data_inicio" />
                            </div>

                            <div>
                                <InputLabel for="data_fim" value="Data de Encerramento (Opcional)" class="!font-black tracking-widest uppercase text-[10px]" />
                                <TextInput id="data_fim" type="date" class="block w-full" v-model="form.data_fim" />
                                <InputError class="mt-2" :message="form.errors.data_fim" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="descricao" value="Descrição e Regras de Coparticipação" class="!font-black tracking-widest uppercase text-[10px]" />
                            <textarea id="descricao" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-body)] focus:ring-primary-dynamic focus:border-primary-dynamic py-3 px-4 text-sm font-bold transition-all" rows="3" v-model="form.descricao" placeholder="Descreva os termos específicos do contrato..."></textarea>
                            <InputError class="mt-2" :message="form.errors.descricao" />
                        </div>

                        <div class="flex justify-end pt-4 gap-3 border-t border-[var(--border-color)]">
                            <SecondaryButton @click="closeModal">
                                <XMarkIcon class="w-4 h-4 mr-2" /> Cancelar
                            </SecondaryButton>
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                <CheckIcon v-if="isEditing" class="w-4 h-4 mr-2" />
                                <PlusIcon v-else class="w-4 h-4 mr-2" />
                                {{ isEditing ? 'Salvar Configurações' : 'Firmar Acordo Operacional' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>

            <!-- Delete Confirmation Modal -->
            <Modal :show="isDeleteModalOpen" @close="isDeleteModalOpen = false">
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="p-3 bg-rose-50 dark:bg-rose-500/10 rounded-full text-rose-500">
                            <ExclamationTriangleIcon class="w-6 h-6"/>
                        </div>
                        <h2 class="text-xl font-black text-[var(--text-heading)]">Excluir Registro de Contrato</h2>
                    </div>

                    <p class="text-sm text-slate-500 font-medium mb-6">
                        Você está prestes a remover permanentemente este contrato operacional. Esta ação impedirá imediatamente o encaminhamento de serviços cruzados entre estas entidades. Deseja prosseguir?
                    </p>

                    <div class="flex justify-end gap-3 pt-6 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="isDeleteModalOpen = false">
                            <XMarkIcon class="w-4 h-4 mr-2" /> Cancelar
                        </SecondaryButton>
                        <DangerButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click="deleteContract">
                            <TrashIcon class="w-4 h-4 mr-2" /> Excluir Contrato
                        </DangerButton>
                    </div>
                </div>
            </Modal>
        </div>
    </AuthenticatedLayout>
</template>
