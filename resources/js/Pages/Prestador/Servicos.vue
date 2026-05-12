<script setup>
import PrestadorLayout from '@/Layouts/PrestadorLayout.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import {
    BuildingStorefrontIcon,
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    CheckCircleIcon,
    TagIcon,
    XMarkIcon,
    CheckIcon,
    EyeIcon,
    EyeSlashIcon,
} from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    prestador: Object,
    servicos: Array,
});

const page = usePage();
const primaryColor = computed(() => page.props.tenant?.primary_color || '#2563eb');

const showModal = ref(false);
const editingServico = ref(null);

const form = useForm({
    nome: '',
    descricao: '',
    valor_acordado: '',
    ativo: true,
});

const openCreateModal = () => {
    editingServico.value = null;
    form.reset();
    form.ativo = true;
    showModal.value = true;
};

const openEditModal = (servico) => {
    editingServico.value = servico;
    form.nome = servico.nome;
    form.descricao = servico.descricao || '';
    form.valor_acordado = servico.valor_acordado || '';
    form.ativo = servico.ativo;
    showModal.value = true;
};

const submit = () => {
    if (editingServico.value) {
        form.put(route('prestador.servicos.update', editingServico.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                showModal.value = false;
                editingServico.value = null;
            }
        });
    } else {
        form.post(route('prestador.servicos.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                showModal.value = false;
            }
        });
    }
};

const deleteServico = (servico) => {
    if (confirm(`Deseja realmente excluir "${servico.nome}" do catálogo?`)) {
        router.delete(route('prestador.servicos.destroy', servico.id), {
            preserveScroll: true,
        });
    }
};

const toggleAtivo = (servico) => {
    router.put(route('prestador.servicos.update', servico.id), {
        nome: servico.nome,
        descricao: servico.descricao,
        valor_acordado: servico.valor_acordado,
        ativo: !servico.ativo,
    }, {
        preserveScroll: true,
    });
};

const formatCurrency = (value) => {
    if (!value) return 'Sob Consulta';
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};
</script>

<template>
    <Head title="Meu Catálogo" />

    <PrestadorLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 w-full">
                <div>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">Catálogo de Serviços e Produtos</h2>
                    <p class="text-[12px] text-[var(--text-muted)] mt-0.5 font-bold">
                        Gerencie o que você oferece aos cooperados.
                    </p>
                </div>
                <PrimaryButton @click="openCreateModal" class="!py-3 !rounded-xl !px-6 shadow-lg shadow-primary-dynamic/20">
                    <PlusIcon class="w-5 h-5 mr-2" /> Adicionar Item
                </PrimaryButton>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            
            <div v-if="$page.props.flash?.success" class="bg-emerald-50 text-emerald-700 p-4 rounded-xl border border-emerald-100 flex items-center">
                <CheckCircleIcon class="w-5 h-5 mr-3 flex-shrink-0" />
                <span class="text-sm font-bold">{{ $page.props.flash.success }}</span>
            </div>

            <!-- Tabela de Serviços -->
            <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-color)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[var(--border-color)]">
                        <thead class="bg-slate-50/50 dark:bg-slate-800/50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Item</th>
                                <th scope="col" class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Descrição</th>
                                <th scope="col" class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Valor</th>
                                <th scope="col" class="px-6 py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                                <th scope="col" class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border-color)] bg-transparent">
                            <tr v-for="servico in servicos" :key="servico.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-primary-dynamic/10 text-primary-dynamic flex items-center justify-center shrink-0">
                                            <TagIcon class="w-4 h-4" />
                                        </div>
                                        <div class="text-sm font-black text-[var(--text-heading)]">{{ servico.nome }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-medium text-[var(--text-muted)] line-clamp-2 max-w-xs">{{ servico.descricao || '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ formatCurrency(servico.valor_acordado) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button @click="toggleAtivo(servico)" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest transition-all cursor-pointer hover:scale-105" :class="servico.ativo ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200 hover:bg-emerald-200' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 hover:bg-slate-200'" :title="servico.ativo ? 'Clique para desativar' : 'Clique para ativar'">
                                        <EyeIcon v-if="servico.ativo" class="w-3 h-3" />
                                        <EyeSlashIcon v-else class="w-3 h-3" />
                                        {{ servico.ativo ? 'Ativo' : 'Inativo' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex justify-end items-center gap-1">
                                        <button @click="openEditModal(servico)" class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-xl transition-all" title="Editar">
                                            <PencilSquareIcon class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteServico(servico)" class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-all" title="Excluir">
                                            <TrashIcon class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="servicos.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <TagIcon class="w-12 h-12 text-slate-200 mx-auto mb-3" />
                                    <p class="text-sm text-[var(--text-muted)] font-bold">Nenhum serviço ou produto cadastrado no catálogo.</p>
                                    <p class="text-xs text-slate-400 mt-1 font-medium">Clique em "Adicionar Item" para começar.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>

        <!-- Modal Criar / Editar -->
        <Modal :show="showModal" @close="showModal = false" max-width="lg">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <p class="text-[10px] font-black text-primary-dynamic uppercase tracking-[0.2em] mb-1">Catálogo</p>
                        <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tighter">
                            {{ editingServico ? 'Editar Item' : 'Novo Item' }}
                        </h2>
                    </div>
                    <button @click="showModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel value="Nome do Serviço/Produto *" />
                        <TextInput 
                            v-model="form.nome" 
                            type="text" 
                            class="mt-1 block w-full" 
                            placeholder="Ex: Consulta Odontológica, Exame de Sangue..."
                            required 
                            autofocus
                        />
                        <InputError :message="form.errors.nome" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel value="Descrição (Opcional)" />
                        <textarea 
                            v-model="form.descricao" 
                            rows="3" 
                            class="mt-1 block w-full text-sm rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic py-3 px-4 font-medium transition-all resize-none" 
                            placeholder="Detalhes sobre o que está incluído..."
                        ></textarea>
                        <InputError :message="form.errors.descricao" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel value="Valor (R$) - Opcional" />
                            <TextInput 
                                v-model="form.valor_acordado" 
                                type="number" 
                                step="0.01"
                                class="mt-1 block w-full" 
                                placeholder="Ex: 150.00"
                            />
                            <InputError :message="form.errors.valor_acordado" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel value="Visibilidade" />
                            <div class="mt-2 flex items-center gap-3">
                                <button type="button" @click="form.ativo = !form.ativo" :class="['relative inline-flex h-7 w-12 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2', form.ativo ? 'bg-emerald-500 focus:ring-emerald-500' : 'bg-slate-300 dark:bg-slate-600 focus:ring-slate-400']">
                                    <span :class="['inline-block h-5 w-5 rounded-full bg-white shadow-lg transition-transform duration-200', form.ativo ? 'translate-x-6' : 'translate-x-1']" />
                                </button>
                                <span class="text-sm font-bold" :class="form.ativo ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'">
                                    {{ form.ativo ? 'Ativo (Visível)' : 'Inativo (Oculto)' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 flex items-center justify-end space-x-3 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="showModal = false" type="button" class="!rounded-full !py-4 !px-8">
                            <XMarkIcon class="w-4 h-4 mr-2" /> Cancelar
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing" class="!rounded-full !py-4 !px-8 shadow-xl shadow-primary-dynamic/20">
                            <CheckIcon v-if="editingServico" class="w-4 h-4 mr-2" />
                            <PlusIcon v-else class="w-4 h-4 mr-2" />
                            {{ editingServico ? 'Salvar Alterações' : 'Adicionar ao Catálogo' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </PrestadorLayout>
</template>

<style scoped>
.text-primary-dynamic { color: v-bind(primaryColor); }
.bg-primary-dynamic { background-color: v-bind(primaryColor) !important; }
.border-primary-dynamic { border-color: v-bind(primaryColor) !important; }
</style>
