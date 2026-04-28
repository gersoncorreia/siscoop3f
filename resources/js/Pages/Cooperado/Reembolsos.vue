<script setup>
import CooperadoLayout from '@/Layouts/CooperadoLayout.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import FileInput from '@/Components/FileInput.vue';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { 
    CurrencyDollarIcon, 
    CalendarIcon, 
    ExclamationCircleIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    DocumentCheckIcon,
    TrashIcon,
    ClipboardDocumentCheckIcon,
    PlusIcon,
    CheckIcon,
    XMarkIcon,
    PaperAirplaneIcon
} from '@heroicons/vue/24/outline';

const page = usePage();
const reembolsos = computed(() => page.props.reembolsos || []);

const isRequestModalOpen = ref(false);

const form = useForm({
    valor_solicitado: '',
    descricao: '',
    arquivo: null,
});



function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
}

function openRequestModal() {
    form.reset();
    form.clearErrors();
    isRequestModalOpen.value = true;
}

function handleFileChange(e) {
    form.arquivo = e.target.files[0];
}

function submitRequest() {
    form.post(route('cooperado.reembolsos.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isRequestModalOpen.value = false;
        },
    });
}

function deleteReembolso(id) {
    if (confirm('Tem certeza que deseja excluir esta solicitação negada para enviar uma nova?')) {
        form.delete(route('cooperado.reembolsos.destroy', id), {
            preserveScroll: true
        });
    }
}
</script>

<template>
    <Head title="Reembolsos" />
    <CooperadoLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">Reembolsos</h2>
                    <p class="text-sm text-[var(--text-muted)] mt-1 font-medium">Histórico de solicitações de reembolso de despesas.</p>
                </div>
                <button @click="openRequestModal" class="btn-primary flex items-center gap-2">
                    <PlusIcon class="w-4 h-4" />
                    Nova Solicitação
                </button>
            </div>
        </template>

        <div class="space-y-4">
            <div
                v-for="reembolso in reembolsos"
                :key="reembolso.id"
                class="zynix-card hover:shadow-lg transition-all duration-300"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 rounded-lg">
                            <CurrencyDollarIcon class="w-5 h-5" />
                        </div>
                        <div>
                            <div class="text-sm font-black text-[var(--text-heading)]">
                                {{ formatCurrency(reembolso.valor_solicitado) }}
                            </div>
                            <div class="flex items-center gap-1.5 mt-0.5 text-xs text-slate-500 font-medium">
                                <CalendarIcon class="w-3.5 h-3.5" />
                                {{ reembolso.created_at }}
                            </div>
                            <div class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                                <span class="font-bold">Descrição:</span> {{ reembolso.descricao }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col items-end gap-2">
                        <span v-if="reembolso.status === 'pendente'" class="flex items-center gap-1.5 w-max px-2 py-1 rounded-md bg-amber-50 text-amber-600 text-[10px] font-black uppercase tracking-widest border border-amber-200">
                            <ExclamationCircleIcon class="w-3 h-3" /> Em Análise
                        </span>
                        <span v-else-if="reembolso.status === 'aprovado' || reembolso.status === 'pago'" class="flex items-center gap-1.5 w-max px-2 py-1 rounded-md bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest border border-emerald-200">
                            <CheckCircleIcon class="w-3 h-3" /> {{ reembolso.status }}
                        </span>
                        <span v-else-if="reembolso.status === 'rejeitado'" class="flex items-center gap-1.5 w-max px-2 py-1 rounded-md bg-rose-50 text-rose-600 text-[10px] font-black uppercase tracking-widest border border-rose-200">
                            <ExclamationTriangleIcon class="w-3 h-3" /> Negado
                        </span>
                        
                        <p v-if="reembolso.status === 'rejeitado' && reembolso.motivo_recusa" class="text-[10px] font-medium text-rose-500 max-w-[200px] bg-rose-50 p-1.5 rounded border border-rose-100">
                            <strong>Motivo:</strong> {{ reembolso.motivo_recusa }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a v-if="reembolso.comprovante" :href="route('cooperado.reembolsos.download', reembolso.id)" target="_blank" class="p-2 rounded-lg bg-slate-100 text-slate-600 hover:text-primary-dynamic hover:bg-primary-50 transition-colors" title="Baixar Comprovante">
                            <DocumentCheckIcon class="w-4 h-4" />
                        </a>
                        <button v-if="reembolso.status === 'rejeitado'" @click="deleteReembolso(reembolso.id)" class="p-2 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors" title="Excluir para refazer">
                            <TrashIcon class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="!reembolsos.length" class="zynix-card py-16 text-center">
                <ClipboardDocumentCheckIcon class="w-12 h-12 mx-auto text-slate-200 dark:text-slate-700 mb-3" />
                <p class="text-xs font-bold text-[var(--text-muted)] uppercase tracking-widest mb-4">Nenhum reembolso solicitado</p>
                <button @click="openRequestModal" class="btn-primary inline-flex items-center gap-2 text-xs">
                    <PlusIcon class="w-4 h-4" />
                    Solicitar Primeiro Reembolso
                </button>
            </div>
        </div>

        <!-- Nova Solicitação Modal -->
        <Modal :show="isRequestModalOpen" @close="isRequestModalOpen = false" max-width="md">
            <div class="p-8">
                <h3 class="text-xl font-black text-[var(--text-heading)] tracking-tight mb-8">Nova Solicitação de Reembolso</h3>

                <form @submit.prevent="submitRequest" class="space-y-6">
                    <div>
                        <InputLabel for="valor_solicitado" value="Valor Gasto (R$)" class="!font-black text-xs uppercase" />
                        <TextInput id="valor_solicitado" type="number" step="0.01" class="block w-full mt-1 font-mono text-lg font-bold" v-model="form.valor_solicitado" required placeholder="0.00" />
                        <InputError class="mt-2" :message="form.errors.valor_solicitado" />
                        <p class="text-[10px] text-slate-500 mt-1">Insira apenas o valor exato comprovado na NFe.</p>
                    </div>
                    
                    <div>
                        <InputLabel for="descricao" value="Descrição Despesa / Prestador" class="!font-black text-xs uppercase" />
                        <TextInput id="descricao" type="text" class="block w-full mt-1" v-model="form.descricao" placeholder="Ex: Consulta Pediátrica Dra. Maria CRM 123" required />
                        <InputError class="mt-2" :message="form.errors.descricao" />
                    </div>

                    <div>
                        <InputLabel for="arquivo" value="Comprovante Fiscal / NFe (MÁX: 10MB)" class="!font-black text-xs uppercase mb-2" />
                        <FileInput v-model="form.arquivo" label="Clique para anexar a NFe ou Comprovante" id="arquivo_reembolso" />
                        <InputError class="mt-2" :message="form.errors.arquivo" />
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="isRequestModalOpen = false" type="button">
                            <XMarkIcon class="w-4 h-4 mr-2" /> Cancelar
                        </SecondaryButton>
                        <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            <PaperAirplaneIcon class="w-4 h-4 mr-2" />
                            {{ form.processing ? 'Enviando...' : 'Enviar Solicitação' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </CooperadoLayout>
</template>
