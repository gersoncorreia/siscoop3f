<script setup>
import CooperadoLayout from '@/Layouts/CooperadoLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import FileInput from '@/Components/FileInput.vue';
import { useForm } from '@inertiajs/vue3';
import { 
    DocumentTextIcon, 
    ArrowDownTrayIcon, 
    ArrowUpTrayIcon, 
    ExclamationTriangleIcon, 
    CheckCircleIcon,
    ExclamationCircleIcon,
    CheckIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';
import { ref } from 'vue';

const page = usePage();
const documentos = computed(() => page.props.documentos || []);

const isUploadModalOpen = ref(false);

const form = useForm({
    titulo: '',
    tipo: '',
    arquivo: null,
});

function openUploadModal() {
    form.reset();
    form.clearErrors();
    isUploadModalOpen.value = true;
}

function handleFileChange(e) {
    form.arquivo = e.target.files[0];
}

function submitFile() {
    form.post(route('cooperado.documentos.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isUploadModalOpen.value = false;
        },
    });
}

function deleteDocumento(id) {
    if (confirm('Tem certeza que deseja excluir e substituir este documento?')) {
        form.delete(route('cooperado.documentos.destroy', id), {
            preserveScroll: true
        });
    }
}

function tipoColor(tipo) {
    const typeStr = (tipo || '').toLowerCase();
    if (typeStr.includes('pdf')) return 'bg-rose-50 text-rose-600';
    if (typeStr.includes('img') || typeStr.includes('foto')) return 'bg-blue-50 text-blue-600';
    return 'bg-slate-100 text-slate-500';
}
</script>

<template>
    <Head title="Documentos" />
    <CooperadoLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">Documentos</h2>
                    <p class="text-sm text-[var(--text-muted)] mt-1 font-medium">Arquivos e documentos vinculados ao seu cadastro.</p>
                </div>
                <button @click="openUploadModal" class="btn-primary flex items-center gap-2">
                    <ArrowUpTrayIcon class="w-4 h-4" /> Anexar Documento
                </button>
            </div>
        </template>

        <div class="zynix-card !p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="zynix-table">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Status / Tipo</th>
                            <th>Data</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="doc in documentos" :key="doc.id" class="group">
                            <td>
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 rounded-lg" :class="tipoColor(doc.tipo)">
                                            <DocumentTextIcon class="w-4 h-4" />
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-[var(--text-heading)]">{{ doc.titulo }}</span>
                                            <span class="text-[9px] font-bold uppercase tracking-widest text-slate-400 mt-0.5">Arquivo: {{ doc.tipo }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span v-if="doc.status === 'pendente'" class="flex items-center gap-1.5 w-max px-2 py-1 rounded-md bg-amber-50 text-amber-600 text-[10px] font-black uppercase tracking-widest">
                                        <ExclamationCircleIcon class="w-3 h-3" /> Em Análise
                                    </span>
                                    <span v-else-if="doc.status === 'aprovado'" class="flex items-center gap-1.5 w-max px-2 py-1 rounded-md bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest">
                                        <CheckCircleIcon class="w-3 h-3" /> Aprovado
                                    </span>
                                    <span v-else-if="doc.status === 'rejeitado'" class="flex items-center gap-1.5 w-max px-2 py-1 rounded-md bg-rose-50 text-rose-600 border border-rose-200 text-[10px] font-black uppercase tracking-widest">
                                        <ExclamationTriangleIcon class="w-3 h-3" /> Correção Requisitada
                                    </span>
                                    <!-- MOTIVO DA REJEICAO -->
                                    <p v-if="doc.status === 'rejeitado' && doc.motivo_rejeicao" class="mt-2 text-xs font-medium text-rose-500 max-w-xs bg-rose-50 p-2 rounded-md">
                                        <strong>Motivo:</strong> {{ doc.motivo_rejeicao }}
                                    </p>
                                </td>
                                <td class="text-xs font-bold text-[var(--text-muted)]">{{ doc.created_at }}</td>
                                <td class="flex justify-end gap-2 pr-4">
                                    <a :href=" route('cooperado.documentos.download', doc.id)" target="_blank" class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-xl transition-all" title="Baixar">
                                        <ArrowDownTrayIcon class="w-4 h-4" />
                                    </a>
                                    <button v-if="doc.status === 'rejeitado'" @click="deleteDocumento(doc.id)" class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-all" title="Excluir para reenviar">
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="!documentos.length" class="py-16 text-center">
                <DocumentTextIcon class="w-12 h-12 mx-auto text-slate-200 dark:text-slate-700 mb-3" />
                <p class="text-xs font-bold text-[var(--text-muted)] uppercase tracking-widest">Nenhum documento anexado.</p>
                <p class="text-[11px] text-[var(--text-muted)] mt-2 max-w-xs mx-auto">
                    Você ainda não fez o upload de nenhum documento.
                </p>
            </div>
        </div>

        <!-- Upload de Novo Documento -->
        <Modal :show="isUploadModalOpen" @close="isUploadModalOpen = false">
            <div class="p-8">
                <h2 class="text-xl font-black text-[var(--text-heading)] tracking-tight mb-8">Anexar Novo Arquivo</h2>
                <form @submit.prevent="submitFile" class="space-y-6">
                    <div>
                        <InputLabel for="titulo" value="Título do Documento" class="!font-black text-xs uppercase" />
                        <TextInput id="titulo" type="text" class="block w-full mt-1" v-model="form.titulo" required placeholder="Ex: CNH Frente, Comprovante de Residência" />
                        <InputError class="mt-2" :message="form.errors.titulo" />
                    </div>
                    
                    <div>
                        <InputLabel for="tipo" value="Tipo (Opcional)" class="!font-black text-xs uppercase" />
                        <TextInput id="tipo" type="text" class="block w-full mt-1" v-model="form.tipo" placeholder="Ex: CNH, Fatura, RG" />
                        <InputError class="mt-2" :message="form.errors.tipo" />
                    </div>

                    <div>
                        <InputLabel for="arquivo" value="Arquivo (MÁX: 10MB)" class="!font-black text-xs uppercase mb-2" />
                        <FileInput v-model="form.arquivo" label="Clique para selecionar o arquivo" id="arquivo_main" />
                        <InputError class="mt-2" :message="form.errors.arquivo" />
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="isUploadModalOpen = false">
                            <XMarkIcon class="w-4 h-4 mr-2" /> Cancelar
                        </SecondaryButton>
                        <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            <ArrowUpTrayIcon class="w-4 h-4 mr-2" /> Fazer Upload
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </CooperadoLayout>
</template>
