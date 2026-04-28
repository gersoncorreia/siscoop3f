<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    ChevronLeftIcon,
    UserIcon,
    IdentificationIcon,
    CalendarIcon,
    MapPinIcon,
    CheckCircleIcon,
    DocumentTextIcon,
    ArrowDownTrayIcon,
    TrashIcon,
    Bars3CenterLeftIcon,
    PlusIcon
} from '@heroicons/vue/24/outline';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    associado: {
        type: Object,
        default: null
    }
});

const form = useForm({
    nome: props.associado?.nome ?? '',
    cpf: props.associado?.cpf ?? '',
    email: props.associado?.email ?? '',
    nascimento: props.associado?.nascimento?.split('T')[0] ?? '',
    endereco: props.associado?.endereco ?? '',
    status: props.associado?.status ?? 'pendente',
    data_adesao: props.associado?.data_adesao?.split('T')[0] ?? new Date().toISOString().split('T')[0],
});

const page = usePage();

const submit = () => {
    if (props.associado) {
        form.put(route('associados.update', props.associado.id));
    } else {
        form.post(route('associados.store'));
    }
};

// --- Gestão de Documentos ---
const docForm = useForm({
    titulo: '',
    tipo: '',
    arquivo: null,
});

const uploadDocumento = () => {
    docForm.post(route('associados.documentos.store', props.associado.id), {
        preserveScroll: true,
        onSuccess: () => {
            docForm.reset();
        }
    });
};

const deleteDocumento = (docId) => {
    if (confirm('Tem certeza que deseja excluir este documento permanentemente?')) {
        useForm({}).delete(route('associados.documentos.destroy', { associado: props.associado.id, documento: docId }), {
            preserveScroll: true
        });
    }
};

const aprovarDocumento = (docId) => {
    useForm({}).post(route('associados.documentos.aprovar', { associado: props.associado.id, documento: docId }), {
        preserveScroll: true
    });
};

const aprovarAdesaoGeral = () => {
    router.post(route('associados.adesoes.aprovar', props.associado.id));
};

const backRoute = computed(() => {
    if (props.associado && props.associado.status === 'analise_documental') {
        return route('associados.adesoes.index');
    }
    return route('associados.index');
});

const isRejectModalOpen = ref(false);
const rejectingDocId = ref(null);
const rejectForm = useForm({
    motivo_rejeicao: ''
});

const confirmarRejeicao = (docId) => {
    rejectingDocId.value = docId;
    rejectForm.reset();
    isRejectModalOpen.value = true;
};

const rejeitarDocumento = () => {
    rejectForm.post(route('associados.documentos.rejeitar', { associado: props.associado.id, documento: rejectingDocId.value }), {
        preserveScroll: true,
        onSuccess: () => {
            isRejectModalOpen.value = false;
        }
    });
};
</script>

<template>
    <Head :title="associado ? 'Editar Associado' : 'Novo Associado'" />

    <AuthenticatedLayout>
        <div class="py-6 sm:py-8 lg:py-12">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <Breadcrumbs />
                        <h1 class="text-2xl lg:text-3xl font-black text-[var(--text-heading)] tracking-tighter mt-2">
                            {{ associado ? 'Ficha do Associado' : 'Novo Cooperado' }}
                        </h1>
                    </div>
                    <div class="flex items-center gap-3">
                        <button 
                            v-if="associado && associado.status === 'analise_documental'"
                            @click="aprovarAdesaoGeral"
                            class="!rounded-full !px-6 !py-2.5 !text-[10px] !font-black !uppercase !tracking-widest bg-emerald-500 text-white hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-500/20 flex items-center gap-2"
                        >
                            <CheckCircleIcon class="w-4 h-4" />
                            Aprovar Adesão
                        </button>
                        <Link :href="backRoute">
                            <SecondaryButton class="!rounded-full !px-5 !py-2.5 !text-[10px] !font-black !uppercase !tracking-widest">
                                <ChevronLeftIcon class="w-4 h-4 mr-1.5" />
                                Voltar
                            </SecondaryButton>
                        </Link>
                    </div>
                </div>
                <form @submit.prevent="submit" class="zynix-card overflow-hidden !p-0">
                    <!-- Form Header Section -->
                    <div class="p-8 border-b border-[var(--border-color)]">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center">
                                <div class="p-3 rounded-2xl bg-primary-dynamic/10 text-primary-dynamic shadow-inner">
                                    <UserIcon class="w-8 h-8" />
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">Informações Cadastrais</h3>
                                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">Gestão de perfil do cooperado</p>
                                </div>
                            </div>
                            <div v-if="associado" class="px-3 py-1 bg-primary-dynamic/10 text-primary-dynamic text-[10px] font-black uppercase tracking-widest rounded-lg border border-primary-dynamic/20 shadow-sm">
                                Modo Edição
                            </div>
                        </div>
                    </div>

                    <div class="p-8 space-y-8">
                        <!-- Grid 1: Nome e CPF -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-1">
                                <InputLabel for="nome" value="Nome Completo" />
                                <TextInput 
                                    id="nome"
                                    v-model="form.nome"
                                    type="text" 
                                    class="w-full"
                                    placeholder="Digite o nome completo"
                                    required
                                />
                                <InputError :message="form.errors.nome" />
                            </div>

                            <div class="space-y-1">
                                <InputLabel for="cpf" value="CPF" />
                                <TextInput 
                                    id="cpf"
                                    v-model="form.cpf"
                                    type="text" 
                                    class="w-full"
                                    placeholder="000.000.000-00"
                                    required
                                />
                                <InputError :message="form.errors.cpf" />
                            </div>
                        </div>

                        <!-- Email Field -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-1">
                                <InputLabel for="email" value="E-mail Pessoal" />
                                <TextInput 
                                    id="email"
                                    v-model="form.email"
                                    type="email" 
                                    class="w-full"
                                    placeholder="exemplo@email.com"
                                    required
                                />
                                <InputError :message="form.errors.email" />
                            </div>
                        </div>

                        <!-- Grid 2: Nascimento e Adesão -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-1">
                                <InputLabel for="nascimento" value="Data de Nascimento" />
                                <TextInput 
                                    id="nascimento"
                                    v-model="form.nascimento"
                                    type="date" 
                                    class="w-full"
                                />
                                <InputError :message="form.errors.nascimento" />
                            </div>

                            <div class="space-y-1">
                                <InputLabel for="data_adesao" value="Data de Adesão" />
                                <TextInput 
                                    id="data_adesao"
                                    v-model="form.data_adesao"
                                    type="date" 
                                    class="w-full"
                                />
                                <InputError :message="form.errors.data_adesao" />
                            </div>
                        </div>

                        <!-- Grid 3: Status -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-1">
                                <InputLabel for="status" value="Status do Associado" />
                                <select 
                                    id="status"
                                    v-model="form.status"
                                    class="rounded-full border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-main)] shadow-sm focus:border-primary-dynamic focus:ring-primary-dynamic focus:ring-offset-0 transition-all text-sm w-full px-6 py-3"
                                >
                                    <option value="pendente">Pendente de Análise</option>
                                    <option value="ativo">Ativo / Regular</option>
                                    <option value="inativo">Inativo / Desfiliado</option>
                                </select>
                                <InputError :message="form.errors.status" />
                            </div>
                        </div>

                        <!-- Full Width: Endereço -->
                        <div class="space-y-1">
                            <InputLabel for="endereco" value="Endereço Completo" />
                            <textarea 
                                id="endereco"
                                v-model="form.endereco"
                                rows="3"
                                class="block w-full px-6 py-4 rounded-[1.5rem] border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-main)] shadow-sm focus:border-primary-dynamic focus:ring-primary-dynamic focus:ring-offset-0 transition-all text-sm resize-none placeholder:text-slate-400"
                                placeholder="Rua, Número, Bairro, Cidade - UF"
                            ></textarea>
                            <InputError :message="form.errors.endereco" />
                        </div>
                    </div>

                    <!-- Form Footer Section -->
                    <div class="p-8 border-t border-[var(--border-color)] flex items-center justify-end space-x-4">
                        <Link :href="route('associados.index')">
                            <SecondaryButton class="!px-8 !py-3 !rounded-full !text-[10px] !font-black !uppercase !tracking-widest">Cancelar</SecondaryButton>
                        </Link>
                        <PrimaryButton :disabled="form.processing" class="!px-10 !py-3 !rounded-full !text-[10px] !font-black !uppercase !tracking-widest shadow-xl shadow-primary-dynamic/20">
                            {{ associado ? 'Atualizar Perfil' : 'Efetivar Cadastro' }}
                        </PrimaryButton>
                    </div>
                </form>

                <!-- Seção de Documentos -->
                <div v-if="associado" class="zynix-card overflow-hidden mt-8 !p-0">
                    <div class="p-8 border-b border-[var(--border-color)]">
                        <div class="flex items-center">
                            <div class="p-3 rounded-2xl bg-amber-500/10 text-amber-500 shadow-inner">
                                <DocumentTextIcon class="w-8 h-8" />
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight tracking-tight">Documentação Digital</h3>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">Contratos e arquivos anexados</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-8">
                        <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 text-green-700 p-4 rounded-xl border border-green-100 flex items-center">
                            <CheckCircleIcon class="w-5 h-5 mr-2" />
                            {{ $page.props.flash.success }}
                        </div>

                        <!-- Lista de Documentos -->
                        <div v-if="associado.documentos && associado.documentos.length > 0" class="mb-8 overflow-hidden rounded-2xl border border-[var(--border-color)]">
                            <table class="zynix-table w-full">
                                <thead>
                                    <tr>
                                        <th class="pl-6">Documento</th>
                                        <th class="text-center">Tipo</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-right pr-6">Ações / Auditoria</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="doc in associado.documentos" :key="doc.id">
                                        <td class="pl-6">
                                            <p class="font-bold text-[var(--text-heading)]">{{ doc.titulo }}</p>
                                            <p v-if="doc.motivo_rejeicao" class="text-[10px] text-rose-500 max-w-xs mt-1 bg-rose-50 p-1.5 rounded">
                                                Motivo da Rejeição: {{ doc.motivo_rejeicao }}
                                            </p>
                                        </td>
                                        <td class="text-center">
                                            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg text-[10px] font-black uppercase tracking-widest border border-slate-200 dark:border-slate-600">
                                                {{ doc.tipo || 'Outros' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span v-if="doc.status === 'pendente'" class="text-[10px] bg-amber-50 text-amber-600 font-black px-2 py-1 rounded-md uppercase tracking-widest">Pendente</span>
                                            <span v-else-if="doc.status === 'aprovado'" class="text-[10px] bg-emerald-50 text-emerald-600 font-black px-2 py-1 rounded-md uppercase tracking-widest">Aprovado</span>
                                            <span v-else-if="doc.status === 'rejeitado'" class="text-[10px] bg-rose-50 text-rose-600 font-black px-2 py-1 rounded-md uppercase tracking-widest">Rejeitado</span>
                                            <span v-else class="text-[10px] bg-slate-100 text-slate-500 font-black px-2 py-1 rounded-md uppercase tracking-widest">N/A</span>
                                        </td>
                                        <td class="text-right pr-6 whitespace-nowrap space-x-1">
                                            <a :href="route('associados.documentos.download', { associado: associado.id, documento: doc.id })" 
                                                class="inline-flex items-center p-2 text-slate-400 hover:text-blue-500 transition-all rounded-full hover:bg-blue-50" title="Baixar Arquivo">
                                                <ArrowDownTrayIcon class="w-4 h-4" />
                                            </a>
                                            <!-- BOTOES DE APROVACAO -- só exibe se não aprovado aprovado (ou sempre exibir para rever) -->
                                            <button v-if="doc.status !== 'aprovado'" @click="aprovarDocumento(doc.id)" class="inline-flex items-center p-2 text-slate-400 hover:text-emerald-500 transition-all rounded-full hover:bg-emerald-50" title="Aprovar Documento">
                                                <CheckCircleIcon class="w-4 h-4" />
                                            </button>
                                            <button v-if="doc.status !== 'rejeitado'" @click="confirmarRejeicao(doc.id)" class="inline-flex items-center p-2 text-slate-400 hover:text-rose-500 transition-all rounded-full hover:bg-rose-50" title="Rejeitar Solicitar Correção">
                                                <Bars3CenterLeftIcon class="w-4 h-4" />
                                            </button>
                                            <button @click="deleteDocumento(doc.id)" class="inline-flex items-center p-2 text-slate-400 hover:text-rose-500 transition-all rounded-full hover:bg-rose-500/10" title="Excluir Definitivo">
                                                <TrashIcon class="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="mb-8 p-6 text-center text-sm text-slate-500 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                            Nenhum documento anexado ainda.
                        </div>

                        <!-- Novo Formulário de Upload -->
                        <form @submit.prevent="uploadDocumento" class="space-y-6 pt-8 border-t border-[var(--border-color)]/30">
                            <h4 class="text-sm font-black text-[var(--text-heading)] mb-6 flex items-center tracking-tight">
                                <PlusIcon class="w-4 h-4 mr-2" />
                                Adicionar Novo Arquivo
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="space-y-1">
                                    <InputLabel value="Título" />
                                    <TextInput v-model="docForm.titulo" type="text" class="w-full text-xs" placeholder="Ex: CNH Frente" required />
                                    <InputError :message="docForm.errors.titulo" />
                                </div>
                                <div class="space-y-1">
                                    <InputLabel value="Tipo" />
                                    <select v-model="docForm.tipo" class="rounded-full border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-main)] shadow-sm focus:border-primary-dynamic focus:ring-primary-dynamic focus:ring-offset-0 transition-all text-xs w-full px-5 py-2.5">
                                        <option value="">Outros</option>
                                        <option value="Identidade">Identidade (RG/CNH)</option>
                                        <option value="Comprovante de Endereço">Comprovante de Endereço</option>
                                        <option value="Contrato">Contrato de Adesão</option>
                                        <option value="Comprovante Renda">Comprovante de Renda</option>
                                    </select>
                                    <InputError :message="docForm.errors.tipo" />
                                </div>
                                <div class="space-y-1">
                                    <InputLabel value="Arquivo" />
                                    <input type="file" @input="docForm.arquivo = $event.target.files[0]" required class="block w-full text-[10px] text-slate-500 file:mr-4 file:py-2 file:px-5 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-primary-dynamic/10 file:text-primary-dynamic hover:file:bg-primary-dynamic/20 transition-all" />
                                    <InputError :message="docForm.errors.arquivo" />
                                </div>
                            </div>
                            <div class="flex justify-end pt-4">
                                <PrimaryButton :disabled="docForm.processing" class="!px-8 !py-3 !rounded-full !text-[10px] !font-black !uppercase !tracking-widest shadow-lg shadow-primary-dynamic/10">
                                    {{ docForm.processing ? 'Enviando...' : 'Fazer Upload' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal Rejeitar Documento -->
        <Modal :show="isRejectModalOpen" @close="isRejectModalOpen = false">
            <div class="p-6">
                <h2 class="text-xl font-black text-rose-500 mb-4 flex items-center">
                    <Bars3CenterLeftIcon class="w-6 h-6 mr-2"/> Recusar Documento
                </h2>
                <p class="text-xs text-slate-500 mb-6 font-medium">Você está reprovando a via deste documento. O cooperado será notificado via e-mail e deverá submeter um novo arquivo substituindo a versão atual. Especifique brevemente qual foi o erro no envio:</p>
                <form @submit.prevent="rejeitarDocumento" class="space-y-4">
                    <div>
                        <InputLabel value="Motivo da Rejeição para o Cooperado:" class="!text-rose-500 uppercase tracking-widest !font-black !text-[10px]" />
                        <textarea v-model="rejectForm.motivo_rejeicao" rows="3" class="mt-2 block w-full text-sm rounded-xl border-slate-200 focus:border-rose-500 focus:ring focus:ring-rose-200 focus:ring-opacity-50" placeholder="A foto está ilegível, nome difere etc..." required></textarea>
                        <InputError :message="rejectForm.errors.motivo_rejeicao" />
                    </div>
                    <div class="flex justify-end pt-4 gap-3">
                        <SecondaryButton @click="isRejectModalOpen = false">Cancelar</SecondaryButton>
                        <PrimaryButton type="submit" class="!bg-rose-600 hover:!bg-rose-700" :disabled="rejectForm.processing">
                            Confirmar Rejeição
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Transição suave para o foco dos inputs */
input, select, textarea {
    outline: none !important;
}
</style>
