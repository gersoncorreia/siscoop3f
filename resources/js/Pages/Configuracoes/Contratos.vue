<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    DocumentTextIcon, 
    PlusIcon, 
    TrashIcon, 
    CheckCircleIcon,
    ExclamationTriangleIcon,
    XMarkIcon,
    CheckIcon,
    BuildingOffice2Icon,
    ArrowLeftIcon,
    PaperClipIcon,
    ArrowDownTrayIcon
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
    outrasEntidades: Array,
    currentTenantId: String,
});

const isModalOpen = ref(false);
const isCancelModalOpen = ref(false);
const isFilesModalOpen = ref(false);
const currentContractId = ref(null);
const selectedContractForFiles = ref(null);
const filePreviews = ref([]);

const form = useForm({
    partner_tenant_id: '',
    minha_funcao: 'contratante', // contratante, prestador
    descricao: '',
    arquivos: [],
});

const openModal = () => {
    form.reset();
    form.minha_funcao = 'contratante';
    filePreviews.value = [];
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    filePreviews.value = [];
};

const handleFilesChange = (event) => {
    const files = Array.from(event.target.files);
    form.arquivos = files;
    filePreviews.value = files.map(file => ({
        name: file.name,
        size: formatBytes(file.size),
    }));
};

const removeFile = (index) => {
    form.arquivos.splice(index, 1);
    filePreviews.value.splice(index, 1);
};

const formatBytes = (bytes) => {
    if (!bytes) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const submitForm = () => {
    form.post(route('configuracoes.contratos.solicitar'), {
        forceFormData: true,
        onSuccess: () => closeModal()
    });
};

const openFilesModal = (contrato) => {
    selectedContractForFiles.value = contrato;
    isFilesModalOpen.value = true;
};

const confirmCancel = (id) => {
    currentContractId.value = id;
    isCancelModalOpen.value = true;
};

const cancelContractRequest = () => {
    form.put(route('configuracoes.contratos.cancelar', currentContractId.value), {
        onSuccess: () => {
            isCancelModalOpen.value = false;
        }
    });
};
</script>

<template>
    <Head title="Contratos Operacionais" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <Breadcrumbs :items="[{ label: 'Ajustes', href: route('configuracoes.index') }, { label: 'Contratos' }]" />
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Contratos e Convênios</h2>
                    <p class="text-sm text-[var(--text-muted)] mt-0.5">Gerencie convênios de atendimento com outras fundações ou cooperativas.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('configuracoes.index')">
                        <SecondaryButton class="!rounded-full !py-2.5">
                            <ArrowLeftIcon class="w-4 h-4 mr-2" /> Voltar para Ajustes
                        </SecondaryButton>
                    </Link>
                    <button @click="openModal()" class="btn-primary flex items-center gap-2 !rounded-full !py-2.5">
                        <PlusIcon class="w-4 h-4" />
                        Solicitar Novo Contrato
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12">

                <!-- Flash Messages -->
                <div v-if="$page.props.flash?.success" class="mb-8 p-4 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20 rounded-2xl font-bold flex items-center gap-3">
                    <CheckCircleIcon class="w-5 h-5 text-emerald-500" />
                    {{ $page.props.flash.success }}
                </div>

                <div v-if="$page.props.flash?.error" class="mb-8 p-4 bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-500/20 rounded-2xl font-bold flex items-center gap-3">
                    <ExclamationTriangleIcon class="w-5 h-5 text-rose-500" />
                    {{ $page.props.flash.error }}
                </div>

                <!-- List Card -->
                <div class="zynix-card overflow-hidden !p-0">
                    <div class="p-8 border-b border-[var(--border-color)]">
                        <h3 class="text-lg font-black text-[var(--text-heading)]">Contratos e Acordos Operacionais</h3>
                        <p class="text-[12px] font-medium text-slate-500">Acompanhe a situação dos contratos ativos e solicitações de parcerias pendentes.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full zynix-table text-left">
                            <thead>
                                <tr>
                                    <th class="p-4 uppercase text-[10px] font-black text-slate-400">Entidade Parceira</th>
                                    <th class="p-4 uppercase text-[10px] font-black text-slate-400">Minha Função</th>
                                    <th class="p-4 uppercase text-[10px] font-black text-slate-400">Status</th>
                                    <th class="p-4 uppercase text-[10px] font-black text-slate-400">Documentos Anexados</th>
                                    <th class="p-4 uppercase text-[10px] font-black text-slate-400">Vigência</th>
                                    <th class="p-4 uppercase text-[10px] font-black text-slate-400 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="c in contratos" :key="c.id" class="border-b border-[var(--border-color)] group hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="p-4 font-black text-sm text-[var(--text-heading)]">
                                        <template v-if="c.tenant_contratante_id === currentTenantId">
                                            {{ c.prestador?.nome }}
                                            <span class="block text-[8px] font-bold text-indigo-500 uppercase">Fundação Prestadora</span>
                                        </template>
                                        <template v-else>
                                            {{ c.contratante?.nome }}
                                            <span class="block text-[8px] font-bold text-teal-500 uppercase">Cooperativa Contratante</span>
                                        </template>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest" :class="c.tenant_contratante_id === currentTenantId ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600' : 'bg-teal-50 dark:bg-teal-500/10 text-teal-600'">
                                            {{ c.tenant_contratante_id === currentTenantId ? 'Contratante' : 'Prestadora' }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <span v-if="c.status === 'ativo'" class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 text-[10px] font-bold uppercase tracking-widest rounded-md">Ativo</span>
                                        <span v-else-if="c.status === 'pendente'" class="px-2.5 py-1 bg-amber-50 dark:bg-amber-500/10 text-amber-600 text-[10px] font-bold uppercase tracking-widest rounded-md">Pendente</span>
                                        <span v-else-if="c.status === 'suspenso'" class="px-2.5 py-1 bg-yellow-50 dark:bg-yellow-500/10 text-yellow-600 text-[10px] font-bold uppercase tracking-widest rounded-md">Suspenso</span>
                                        <span v-else class="px-2.5 py-1 bg-rose-50 dark:bg-rose-500/10 text-rose-600 text-[10px] font-bold uppercase tracking-widest rounded-md">Encerrado</span>
                                    </td>
                                    <td class="p-4">
                                        <button 
                                            v-if="c.arquivos && c.arquivos.length > 0"
                                            @click="openFilesModal(c)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 dark:bg-blue-500/10 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-bold transition-all"
                                        >
                                            <PaperClipIcon class="w-3.5 h-3.5" />
                                            {{ c.arquivos.length }} arquivo(s)
                                        </button>
                                        <span v-else class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Nenhum anexo</span>
                                    </td>
                                    <td class="p-4 text-xs font-semibold text-[var(--text-muted)]">
                                        <span v-if="c.status === 'ativo'">
                                            {{ new Date(c.data_inicio).toLocaleDateString('pt-BR') }} 
                                            <span v-if="c.data_fim"> até {{ new Date(c.data_fim).toLocaleDateString('pt-BR') }}</span>
                                            <span v-else class="text-emerald-500 font-bold uppercase text-[9px] block">Por tempo indeterminado</span>
                                        </span>
                                        <span v-else class="text-slate-400">Aguardando ativação</span>
                                    </td>
                                    <td class="p-4 flex gap-2 justify-end items-center">
                                        <button 
                                            v-if="c.arquivos && c.arquivos.length > 0"
                                            @click="openFilesModal(c)"
                                            class="p-2 rounded-lg text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 font-bold text-xs"
                                            title="Ver Documentos"
                                        >
                                            <DocumentTextIcon class="w-4 h-4" />
                                        </button>
                                        <button v-if="c.status === 'pendente'" @click="confirmCancel(c.id)" class="px-3 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-500/10 rounded-lg text-xs font-bold transition flex items-center gap-1.5" title="Cancelar Solicitação">
                                            <XMarkIcon class="w-4 h-4" /> Cancelar
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="contratos.length === 0">
                                    <td colspan="6" class="p-8 text-center text-[11px] font-bold uppercase tracking-widest text-slate-400">
                                        Nenhum convênio operacional ativo ou solicitado.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Solicitar Modal -->
                <Modal :show="isModalOpen" @close="closeModal">
                    <div class="p-6">
                        <h2 class="text-xl font-black text-[var(--text-heading)] flex items-center gap-2 mb-6">
                            <BuildingOffice2Icon class="w-5 h-5 text-primary-dynamic"/>
                            Solicitar Acordo de Parceria / Convênio
                        </h2>

                        <form @submit.prevent="submitForm" class="space-y-6">
                            <div>
                                <InputLabel for="partner" value="Entidade Conveniada (Parceira)" class="!font-black tracking-widest uppercase text-[10px]" />
                                <select id="partner" class="block w-full border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 rounded-xl text-xs font-bold text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic transition-all py-2.5" v-model="form.partner_tenant_id" required>
                                    <option value="" disabled>Selecione...</option>
                                    <option v-for="t in outrasEntidades" :key="t.id" :value="t.id">{{ t.nome }} ({{ t.tipo === 'fundacao' ? 'Fundação' : 'Cooperativa' }})</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.partner_tenant_id" />
                            </div>

                            <div>
                                <InputLabel for="minha_funcao" value="Qual será a função de nossa Entidade no contrato?" class="!font-black tracking-widest uppercase text-[10px]" />
                                <select id="minha_funcao" class="block w-full border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 rounded-xl text-xs font-bold text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic transition-all py-2.5" v-model="form.minha_funcao" required>
                                    <option value="contratante">Contratante (Iremos utilizar os serviços da parceira)</option>
                                    <option value="prestador">Prestador (Iremos prestar serviços para a parceira)</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.minha_funcao" />
                            </div>

                            <div>
                                <InputLabel for="descricao" value="Descrição da Proposta" class="!font-black tracking-widest uppercase text-[10px]" />
                                <textarea id="descricao" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-body)] focus:ring-primary-dynamic focus:border-primary-dynamic py-3 px-4 text-sm font-bold transition-all" rows="3" v-model="form.descricao" placeholder="Descreva os termos propostos para este convênio..."></textarea>
                                <InputError class="mt-2" :message="form.errors.descricao" />
                            </div>

                            <!-- Upload de Arquivos / Contratos Firmados -->
                            <div>
                                <InputLabel value="Arquivos do Contrato Firmado (PDF, DOCX, Imagens)" class="!font-black tracking-widest uppercase text-[10px]" />
                                <div class="mt-2 flex flex-col items-center justify-center p-6 border-2 border-dashed border-[var(--border-color)] rounded-xl bg-slate-50/50 dark:bg-slate-900/40 hover:bg-slate-100/50 transition-all cursor-pointer relative">
                                    <input 
                                        type="file" 
                                        multiple 
                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
                                        @change="handleFilesChange" 
                                        class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                                    />
                                    <PaperClipIcon class="w-8 h-8 text-primary-dynamic mb-2" />
                                    <p class="text-xs font-black text-[var(--text-heading)]">Clique ou arraste os arquivos assinados do contrato</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">PDF, DOCX, JPG ou PNG (Máximo 10MB por arquivo)</p>
                                </div>

                                <!-- Previews dos Arquivos Selecionados -->
                                <div v-if="filePreviews.length > 0" class="mt-3 space-y-2">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Arquivos Selecionados ({{ filePreviews.length }}):</p>
                                    <div v-for="(file, idx) in filePreviews" :key="idx" class="flex items-center justify-between p-2.5 bg-slate-100 dark:bg-slate-800/60 rounded-lg text-xs">
                                        <div class="flex items-center gap-2 truncate">
                                            <DocumentTextIcon class="w-4 h-4 text-primary-dynamic flex-shrink-0" />
                                            <span class="font-bold text-[var(--text-heading)] truncate">{{ file.name }}</span>
                                            <span class="text-[10px] text-slate-400 font-semibold">({{ file.size }})</span>
                                        </div>
                                        <button type="button" @click="removeFile(idx)" class="text-rose-500 hover:text-rose-700 p-1" title="Remover">
                                            <XMarkIcon class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors.arquivos" />
                            </div>

                            <div class="flex justify-end pt-4 gap-3 border-t border-[var(--border-color)]">
                                <SecondaryButton @click="closeModal">
                                    <XMarkIcon class="w-4 h-4 mr-2" /> Cancelar
                                </SecondaryButton>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    <PlusIcon class="w-4 h-4 mr-2" />
                                    Enviar Solicitação
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </Modal>

                <!-- Modal Área Reservada de Documentos -->
                <Modal :show="isFilesModalOpen" @close="isFilesModalOpen = false">
                    <div class="p-6" v-if="selectedContractForFiles">
                        <div class="flex items-center justify-between pb-4 border-b border-[var(--border-color)] mb-6">
                            <div>
                                <p class="text-[10px] font-black text-primary-dynamic uppercase tracking-widest">Documentos Anexados</p>
                                <h3 class="text-lg font-black text-[var(--text-heading)] mt-0.5">
                                    Contrato: {{ selectedContractForFiles.contratante?.nome }} & {{ selectedContractForFiles.prestador?.nome }}
                                </h3>
                            </div>
                            <button @click="isFilesModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600">
                                <XMarkIcon class="w-5 h-5" />
                            </button>
                        </div>

                        <div class="space-y-3">
                            <div 
                                v-for="(file, index) in (selectedContractForFiles.arquivos || [])" 
                                :key="index"
                                class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-[var(--border-color)] hover:border-primary-dynamic/40 transition-all"
                            >
                                <div class="flex items-center gap-3 truncate">
                                    <div class="p-2.5 rounded-lg bg-primary-dynamic/10 text-primary-dynamic">
                                        <DocumentTextIcon class="w-6 h-6" />
                                    </div>
                                    <div class="truncate">
                                        <p class="font-black text-sm text-[var(--text-heading)] truncate">{{ file.nome_original }}</p>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">
                                            {{ formatBytes(file.tamanho) }} • Anetado em {{ new Date(file.uploaded_at || Date.now()).toLocaleDateString('pt-BR') }}
                                        </p>
                                    </div>
                                </div>

                                <a 
                                    :href="route('configuracoes.contratos.download-arquivo', { id: selectedContractForFiles.id, index: index })" 
                                    target="_blank"
                                    class="px-4 py-2 bg-primary-dynamic text-white text-xs font-bold rounded-lg hover:brightness-110 transition-all flex items-center gap-1.5 flex-shrink-0 shadow-sm"
                                >
                                    <ArrowDownTrayIcon class="w-4 h-4" /> Baixar
                                </a>
                            </div>

                            <div v-if="!selectedContractForFiles.arquivos || selectedContractForFiles.arquivos.length === 0" class="py-12 text-center text-slate-400 text-xs font-bold uppercase tracking-widest">
                                Nenhum arquivo físico anexado a este contrato.
                            </div>
                        </div>

                        <div class="flex justify-end pt-6 mt-6 border-t border-[var(--border-color)]">
                            <SecondaryButton @click="isFilesModalOpen = false">
                                Fechar
                            </SecondaryButton>
                        </div>
                    </div>
                </Modal>

                <!-- Cancel Solicitation Confirmation Modal -->
                <Modal :show="isCancelModalOpen" @close="isCancelModalOpen = false">
                    <div class="p-6">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="p-3 bg-rose-50 dark:bg-rose-500/10 rounded-full text-rose-500">
                                <ExclamationTriangleIcon class="w-6 h-6"/>
                            </div>
                            <h2 class="text-xl font-black text-[var(--text-heading)]">Cancelar Solicitação</h2>
                        </div>

                        <p class="text-sm text-slate-500 font-medium mb-6">
                            Você está prestes a cancelar esta solicitação de parceria pendente. Deseja prosseguir?
                        </p>

                        <div class="flex justify-end gap-3 pt-6 border-t border-[var(--border-color)]">
                            <SecondaryButton @click="isCancelModalOpen = false">
                                <XMarkIcon class="w-4 h-4 mr-2" /> Manter Pendente
                            </SecondaryButton>
                            <DangerButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click="cancelContractRequest">
                                <TrashIcon class="w-4 h-4 mr-2" /> Sim, Cancelar
                            </DangerButton>
                        </div>
                    </div>
                </Modal>
        </div>
    </AuthenticatedLayout>
</template>
