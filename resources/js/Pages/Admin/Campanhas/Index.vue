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
    MegaphoneIcon,
    PhotoIcon,
    UsersIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    campanhas: Object,
    tipos: Object,
    publicosAlvo: Object,
    filters: Object,
});

const isModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const editingCampanha = ref(null);
const campanhaToDelete = ref(null);
const imagePreview = ref(null);

const form = useForm({
    titulo: '',
    descricao: '',
    tipo: '',
    publico_alvo: '',
    data_inicio: '',
    data_fim: '',
    meta_participantes: '',
    notificar_membros: false,
    imagem: null,
    ativo: true
});

const getStatusInfo = (c) => {
    const hoje = new Date().toISOString().split('T')[0];
    if (!c.ativo) return { label: 'Inativa', class: 'bg-slate-100 text-slate-500' };
    if (c.data_inicio > hoje) return { label: 'Programada', class: 'bg-amber-50 text-amber-600 border-amber-200' };
    if (c.data_fim && c.data_fim < hoje) return { label: 'Encerrada', class: 'bg-slate-200 text-slate-600' };
    return { label: 'Vigente', class: 'bg-emerald-50 text-emerald-600 border-emerald-200' };
};

const openCreateModal = () => {
    editingCampanha.value = null;
    form.reset();
    imagePreview.value = null;
    isModalOpen.value = true;
};

const openEditModal = (c) => {
    editingCampanha.value = c;
    form.titulo = c.titulo;
    form.descricao = c.descricao;
    form.tipo = c.tipo;
    form.publico_alvo = c.publico_alvo;
    form.data_inicio = c.data_inicio;
    form.data_fim = c.data_fim;
    form.meta_participantes = c.meta_participantes;
    form.notificar_membros = c.notificar_membros;
    form.ativo = c.ativo;
    form.imagem = null; // keep existing by default
    imagePreview.value = c.imagem_path ? `/storage/${c.imagem_path}` : null;
    isModalOpen.value = true;
};

const onImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.imagem = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    if (editingCampanha.value) {
        form.post(route('admin.campanhas.update', editingCampanha.value.id), {
            forceFormData: true, // For file upload in update
            onSuccess: () => { isModalOpen.value = false; form.reset(); }
        });
    } else {
        form.post(route('admin.campanhas.store'), {
            onSuccess: () => { isModalOpen.value = false; form.reset(); }
        });
    }
};

const confirmDelete = (c) => {
    campanhaToDelete.value = c;
    isDeleteModalOpen.value = true;
};

const deleteCampanha = () => {
    router.delete(route('admin.campanhas.destroy', campanhaToDelete.value.id), {
        onSuccess: () => { isDeleteModalOpen.value = false; }
    });
};

const toggleAtivo = (id) => {
    router.post(route('admin.campanhas.toggle', id));
};
</script>

<template>
    <Head title="Campanhas de Saúde" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <Breadcrumbs :items="[{ label: 'Admin' }, { label: 'Campanhas de Saúde' }]" />
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Campanhas de Saúde</h2>
                    <p class="text-sm text-[var(--text-muted)] mt-0.5">Gerencie ações preventivas, vacinações e programas especiais.</p>
                </div>
                <div>
                    <PrimaryButton @click="openCreateModal" class="shadow-xl">
                        <PlusIcon class="w-4 h-4 mr-2" />
                        Nova Campanha
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="pb-12 space-y-6">
            <!-- Filtros -->
            <div class="zynix-card p-4 flex gap-4 bg-[var(--bg-surface)]">
                <select class="rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-sm">
                    <option value="">Todos os Tipos</option>
                    <option v-for="(lbl, k) in tipos" :key="k" :value="k">{{ lbl }}</option>
                </select>
                <select class="rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-sm">
                    <option value="">Todos os Status</option>
                    <option value="ativas">Ativas</option>
                    <option value="inativas">Inativas</option>
                </select>
            </div>

            <div v-if="!campanhas.data || campanhas.data.length === 0" class="zynix-card p-12 text-center">
                <MegaphoneIcon class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Nenhuma campanha cadastrada.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="c in campanhas.data" :key="c.id" class="zynix-card flex flex-col overflow-hidden group">
                    <div class="h-32 bg-slate-200 dark:bg-slate-800 relative">
                        <img v-if="c.imagem_path" :src="`/storage/${c.imagem_path}`" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                            <PhotoIcon class="w-10 h-10" />
                        </div>
                        <div class="absolute top-3 right-3">
                            <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-widest rounded-md border shadow-sm backdrop-blur-sm" :class="getStatusInfo(c).class">
                                {{ getStatusInfo(c).label }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-5 flex-grow">
                        <div class="flex gap-2 mb-2">
                            <span class="px-2 py-0.5 bg-primary-dynamic/10 text-primary-dynamic text-[9px] font-black uppercase tracking-widest rounded">
                                {{ tipos[c.tipo] || c.tipo }}
                            </span>
                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 text-[9px] font-black uppercase tracking-widest rounded">
                                {{ publicosAlvo[c.publico_alvo] || c.publico_alvo }}
                            </span>
                        </div>
                        <h3 class="text-lg font-black text-[var(--text-heading)] leading-tight mb-2">{{ c.titulo }}</h3>
                        <p class="text-sm text-slate-500 line-clamp-2 mb-4">{{ c.descricao }}</p>

                        <div class="text-xs font-bold text-slate-400 mb-3">
                            <p>Período: {{ new Date(c.data_inicio).toLocaleDateString('pt-BR') }} 
                               {{ c.data_fim ? 'até ' + new Date(c.data_fim).toLocaleDateString('pt-BR') : '' }}</p>
                        </div>

                        <div v-if="c.meta_participantes" class="mt-auto">
                            <div class="flex justify-between text-xs font-bold mb-1">
                                <span class="text-slate-500"><UsersIcon class="w-3.5 h-3.5 inline mr-1" />Participantes</span>
                                <span class="text-[var(--text-heading)]">{{ c.total_participantes || 0 }} / {{ c.meta_participantes }}</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5">
                                <div class="bg-primary-dynamic h-1.5 rounded-full" :style="{ width: Math.min(((c.total_participantes || 0) / c.meta_participantes) * 100, 100) + '%' }"></div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border-t border-[var(--border-color)] bg-slate-50 dark:bg-slate-800/30 flex justify-between items-center">
                        <button @click="toggleAtivo(c.id)" class="text-[10px] font-black uppercase tracking-widest" :class="c.ativo ? 'text-slate-400 hover:text-slate-600' : 'text-emerald-500 hover:text-emerald-600'">
                            {{ c.ativo ? 'Desativar' : 'Ativar' }}
                        </button>
                        <div class="flex gap-2">
                            <button @click="openEditModal(c)" class="p-1.5 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg">
                                <PencilSquareIcon class="w-4 h-4" />
                            </button>
                            <button @click="confirmDelete(c)" class="p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-lg">
                                <TrashIcon class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Modal -->
        <Modal :show="isModalOpen" @close="isModalOpen = false" max-width="2xl">
            <div class="p-8">
                <div class="flex justify-between mb-6">
                    <h2 class="text-xl font-black text-[var(--text-heading)]">{{ editingCampanha ? 'Editar Campanha' : 'Nova Campanha' }}</h2>
                    <button @click="isModalOpen = false"><XMarkIcon class="w-6 h-6 text-slate-400" /></button>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <InputLabel value="Título da Campanha" class="text-[10px] font-black uppercase tracking-widest" />
                        <TextInput v-model="form.titulo" class="w-full mt-1" required />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Tipo" class="text-[10px] font-black uppercase tracking-widest" />
                            <select v-model="form.tipo" class="block w-full mt-1 rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-sm" required>
                                <option v-for="(lbl, k) in tipos" :key="k" :value="k">{{ lbl }}</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Público Alvo" class="text-[10px] font-black uppercase tracking-widest" />
                            <select v-model="form.publico_alvo" class="block w-full mt-1 rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-sm" required>
                                <option v-for="(lbl, k) in publicosAlvo" :key="k" :value="k">{{ lbl }}</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Descrição Detalhada" class="text-[10px] font-black uppercase tracking-widest" />
                        <textarea v-model="form.descricao" rows="3" class="w-full mt-1 rounded-xl border-[var(--border-color)] bg-[var(--bg-main)]"></textarea>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <InputLabel value="Data Início" class="text-[10px] font-black uppercase tracking-widest" />
                            <TextInput type="date" v-model="form.data_inicio" class="w-full mt-1 text-sm" required />
                        </div>
                        <div>
                            <InputLabel value="Data Fim" class="text-[10px] font-black uppercase tracking-widest" />
                            <TextInput type="date" v-model="form.data_fim" class="w-full mt-1 text-sm" />
                        </div>
                        <div>
                            <InputLabel value="Meta (Participantes)" class="text-[10px] font-black uppercase tracking-widest" />
                            <TextInput type="number" v-model="form.meta_participantes" class="w-full mt-1 text-sm" />
                        </div>
                    </div>

                    <div class="flex gap-4 items-center">
                        <div class="flex-grow">
                            <InputLabel value="Imagem da Campanha" class="text-[10px] font-black uppercase tracking-widest" />
                            <input type="file" @change="onImageChange" accept="image/*" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-primary-dynamic/10 file:text-primary-dynamic hover:file:bg-primary-dynamic/20" />
                        </div>
                        <div v-if="imagePreview" class="w-20 h-20 shrink-0 rounded-lg overflow-hidden bg-slate-100 border">
                            <img :src="imagePreview" class="w-full h-full object-cover" />
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" id="notificar" v-model="form.notificar_membros" class="rounded border-slate-300 text-primary-dynamic">
                        <label for="notificar" class="text-sm font-bold text-slate-600">Disparar notificação (App/Email) para o público alvo</label>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="isModalOpen = false" type="button">Cancelar</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">Salvar Campanha</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="isDeleteModalOpen" @close="isDeleteModalOpen = false" max-width="sm">
            <div class="p-6 text-center">
                <h3 class="text-lg font-black text-rose-500 mb-2">Excluir Campanha?</h3>
                <p class="text-sm text-slate-500 mb-6">Esta ação é irreversível.</p>
                <div class="flex justify-center gap-3">
                    <SecondaryButton @click="isDeleteModalOpen = false">Cancelar</SecondaryButton>
                    <DangerButton @click="deleteCampanha">Sim, Excluir</DangerButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
