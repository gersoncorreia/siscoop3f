<script setup>
import CooperadoLayout from '@/Layouts/CooperadoLayout.vue';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { 
    UserGroupIcon, 
    UserIcon, 
    PlusIcon, 
    IdentificationIcon, 
    CalendarIcon,
    DocumentTextIcon,
    ArrowDownTrayIcon,
    TrashIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    CheckIcon,
    ArrowUpTrayIcon,
    ShareIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import FileInput from '@/Components/FileInput.vue';
import { 
    PencilSquareIcon,
    CloudArrowUpIcon
} from '@heroicons/vue/24/outline';

const page = usePage();
const dependentes = computed(() => page.props.dependentes || []);

const copiedDepId = ref(null);
const copyDepMagicLink = (dep) => {
    const token = dep.access_token || 'temp_' + dep.id;
    const url = `${window.location.protocol}//${window.location.host}/acesso-dependente/${token}`;
    navigator.clipboard.writeText(url);
    copiedDepId.value = dep.id;
    setTimeout(() => copiedDepId.value = null, 2000);
};

// --- Gestão de Dependentes (Modal) ---
const isDepModalOpen = ref(false);
const editingDep = ref(null);

const depForm = useForm({
    nome: '',
    cpf: '',
    rg: '',
    cartao_sus: '',
    data_nascimento: '',
    parentesco: '',
    tipo_dependencia: 'legal',
    autoriza_acesso: false,
    email: '',
});

function openDepModal(dep = null) {
    editingDep.value = dep;
    if (dep) {
        depForm.nome = dep.nome || '';
        depForm.cpf = dep.raw_cpf || dep.cpf || '';
        depForm.rg = dep.rg || '';
        depForm.cartao_sus = dep.cartao_sus || '';
        depForm.data_nascimento = dep.data_nascimento || '';
        depForm.parentesco = dep.parentesco || '';
        depForm.tipo_dependencia = dep.tipo_dependencia || 'legal';
        depForm.autoriza_acesso = !!dep.autoriza_acesso;
        depForm.email = dep.email || '';
    } else {
        depForm.reset();
        depForm.tipo_dependencia = 'legal';
    }
    depForm.clearErrors();
    isDepModalOpen.value = true;
}

function submitDep() {
    if (editingDep.value) {
        depForm.put(route('cooperado.dependentes.update', editingDep.value.id), {
            onSuccess: () => { isDepModalOpen.value = false; }
        });
    } else {
        depForm.post(route('cooperado.dependentes.store'), {
            onSuccess: () => { isDepModalOpen.value = false; }
        });
    }
}

function deleteDep(id) {
    if (confirm('Tem certeza que deseja remover este dependente?')) {
        useForm({}).delete(route('cooperado.dependentes.destroy', id));
    }
}

// --- Gestão de Documentos (Modal) ---
const isDocModalOpen = ref(false);
const selectedDepId = ref(null);

const docForm = useForm({
    titulo: '',
    tipo: '',
    arquivo: null,
    dependente_id: null,
});

function openDocModal(depId) {
    selectedDepId.value = depId;
    docForm.reset();
    docForm.dependente_id = depId;
    docForm.clearErrors();
    isDocModalOpen.value = true;
}

function submitDoc() {
    docForm.post(route('cooperado.documentos.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isDocModalOpen.value = false;
        }
    });
}

function deleteDoc(docId) {
    if (confirm('Excluir este documento rejeitado para enviar um novo?')) {
        useForm({}).delete(route('cooperado.documentos.destroy', docId));
    }
}

function statusColor(status) {
    if (status === 'ativo' || status === 'aprovado') return 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400';
    if (status === 'inativo' || status === 'rejeitado') return 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400';
    return 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400';
}
</script>

<template>
    <Head title="Dependentes" />
    <CooperadoLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">Dependentes</h2>
                    <p class="text-sm text-[var(--text-muted)] mt-1 font-medium">Familiares e dependentes vinculados.</p>
                </div>
                <button @click="openDepModal()" class="btn-primary flex items-center gap-2">
                    <PlusIcon class="w-4 h-4" /> Adicionar Dependente
                </button>
            </div>
        </template>

        <div v-if="dependentes.length" class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div
                v-for="dep in dependentes"
                :key="dep.id"
                class="zynix-card overflow-hidden hover:shadow-xl transition-all duration-300 !p-0"
            >
                <!-- Dep Header -->
                <div class="p-6 border-b border-[var(--border-color)]">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-2xl bg-primary-dynamic/10 text-primary-dynamic shadow-inner">
                                <UserIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <h4 class="text-base font-black text-[var(--text-heading)] leading-none">{{ dep.nome }}</h4>
                                <p class="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-widest mt-2 flex items-center gap-2">
                                    {{ dep.parentesco }}
                                    <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                    <span>{{ dep.cpf || 'CPF não informado' }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button 
                                @click="copyDepMagicLink(dep)" 
                                :class="copiedDepId === dep.id ? 'bg-emerald-500 text-white' : 'bg-primary-dynamic/10 text-primary-dynamic hover:bg-primary-dynamic hover:text-white'"
                                class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider inline-flex items-center gap-1.5 transition-all shadow-sm"
                                title="Copiar Link Mágico de Acesso do Dependente (Validação 5 dígitos CPF)"
                            >
                                <component :is="copiedDepId === dep.id ? CheckIcon : ShareIcon" class="w-3.5 h-3.5" />
                                {{ copiedDepId === dep.id ? 'Copiado!' : 'Link Mágico' }}
                            </button>
                            <button @click="openDepModal(dep)" class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-xl transition-all" title="Editar">
                                <PencilSquareIcon class="w-4 h-4" />
                            </button>
                            <button @click="deleteDep(dep.id)" class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-all" title="Remover">
                                <TrashIcon class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Dep Docs -->
                <div class="p-6 bg-slate-100/80 dark:bg-slate-800/40 border-t border-[var(--border-color)]">
                    <div class="flex items-center justify-between mb-4">
                        <h5 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-800 dark:text-slate-200">Documentação do Dependente</h5>
                        <button @click="openDocModal(dep.id)" class="text-[10px] font-black text-primary-dynamic hover:text-primary-dynamic/70 uppercase tracking-widest flex items-center gap-1 transition-all">
                            <PlusIcon class="w-3 h-3 stroke-[3px]" /> Anexar Via
                        </button>
                    </div>

                    <div v-if="dep.documentos && dep.documentos.length > 0" class="space-y-3">
                        <div v-for="doc in dep.documentos" :key="doc.id" class="flex items-center justify-between p-3 rounded-xl bg-[var(--bg-card)] border border-[var(--border-color)] shadow-sm">
                            <div class="flex items-center gap-3">
                                <DocumentTextIcon class="w-5 h-5 text-slate-400" />
                                <div>
                                    <p class="text-xs font-bold text-[var(--text-heading)]">{{ doc.titulo }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-tighter" :class="statusColor(doc.status)">
                                            {{ doc.status }}
                                        </span>
                                        <p v-if="doc.status === 'rejeitado'" class="text-[9px] text-rose-500 font-medium italic">Motivo: {{ doc.motivo_rejeicao }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <a :href="route('cooperado.documentos.download', doc.id)" class="p-1.5 text-slate-300 hover:text-primary-dynamic">
                                    <ArrowDownTrayIcon class="w-4 h-4" />
                                </a>
                                <button v-if="doc.status === 'rejeitado'" @click="deleteDoc(doc.id)" class="p-1.5 text-slate-300 hover:text-rose-500">
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-6 border border-dashed border-slate-400 dark:border-slate-600 rounded-xl bg-white/50 dark:bg-black/20">
                        <p class="text-[11px] text-slate-800 dark:text-slate-300 font-black uppercase tracking-wider">Nenhum documento anexado.</p>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="zynix-card py-16 text-center">
            <UserGroupIcon class="w-12 h-12 mx-auto text-slate-200 dark:text-slate-700 mb-3" />
            <p class="text-xs font-bold text-[var(--text-muted)] uppercase tracking-widest">Nenhum dependente registrado</p>
            <p class="text-[11px] text-[var(--text-muted)] mt-4 max-w-xs mx-auto">
                Adicione seus dependentes (filhos, cônjuges, etc) para que a administração possa validar a elegibilidade aos convênios.
            </p>
        </div>

        <!-- Modal Dependente -->
        <Modal :show="isDepModalOpen" @close="isDepModalOpen = false">
            <div class="p-8">
                <h3 class="text-xl font-black text-[var(--text-heading)] tracking-tight mb-8">
                    {{ editingDep ? 'Editar Dados do Dependente' : 'Novo Dependente' }}
                </h3>
                <form @submit.prevent="submitDep" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="sm:col-span-2">
                            <InputLabel for="nome" value="Nome Completo do Dependente *" class="!font-black text-[10px] uppercase tracking-widest" />
                            <TextInput id="nome" v-model="depForm.nome" type="text" class="w-full mt-1" placeholder="Ex: Maria Eduarda Silva" required />
                            <InputError :message="depForm.errors.nome" />
                        </div>
                        <div>
                            <InputLabel for="cpf" value="CPF" class="!font-black text-[10px] uppercase tracking-widest" />
                            <TextInput id="cpf" v-model="depForm.cpf" type="text" class="w-full mt-1" placeholder="000.000.000-00" />
                            <InputError :message="depForm.errors.cpf" />
                        </div>
                        <div>
                            <InputLabel for="rg" value="RG / Órgão Emissor" class="!font-black text-[10px] uppercase tracking-widest" />
                            <TextInput id="rg" v-model="depForm.rg" type="text" class="w-full mt-1" placeholder="Ex: 1234567 SSP/PA" />
                            <InputError :message="depForm.errors.rg" />
                        </div>
                        <div>
                            <InputLabel for="cartao_sus" value="Cartão SUS" class="!font-black text-[10px] uppercase tracking-widest" />
                            <TextInput id="cartao_sus" v-model="depForm.cartao_sus" type="text" class="w-full mt-1" placeholder="Nº do Cartão SUS" />
                            <InputError :message="depForm.errors.cartao_sus" />
                        </div>
                        <div>
                            <InputLabel for="nascimento" value="Data de Nascimento" class="!font-black text-[10px] uppercase tracking-widest" />
                            <TextInput id="nascimento" v-model="depForm.data_nascimento" type="date" class="w-full mt-1" />
                            <InputError :message="depForm.errors.data_nascimento" />
                        </div>
                        <div>
                            <InputLabel for="parentesco" value="Grau de Parentesco *" class="!font-black text-[10px] uppercase tracking-widest" />
                            <select id="parentesco" v-model="depForm.parentesco" class="w-full mt-1 rounded-full border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-main)] focus:ring-primary-dynamic focus:border-primary-dynamic py-2.5 px-4 text-sm font-medium" required>
                                <option value="" disabled class="bg-[var(--bg-card)]">Selecione...</option>
                                <option value="Cônjuge" class="bg-[var(--bg-card)] text-[var(--text-main)]">Cônjuge / Companheiro(a)</option>
                                <option value="Filho(a)" class="bg-[var(--bg-card)] text-[var(--text-main)]">Filho(a)</option>
                                <option value="Pai/Mãe" class="bg-[var(--bg-card)] text-[var(--text-main)]">Pai / Mãe</option>
                                <option value="Enteado(a)" class="bg-[var(--bg-card)] text-[var(--text-main)]">Enteado(a)</option>
                                <option value="Irmão(ã)" class="bg-[var(--bg-card)] text-[var(--text-main)]">Irmão(ã)</option>
                                <option value="Outro" class="bg-[var(--bg-card)] text-[var(--text-main)]">Outro / Agregado</option>
                            </select>
                            <InputError :message="depForm.errors.parentesco" />
                        </div>
                        <div>
                            <InputLabel for="tipo_dependencia" value="Tipo de Dependência *" class="!font-black text-[10px] uppercase tracking-widest" />
                            <select id="tipo_dependencia" v-model="depForm.tipo_dependencia" class="w-full mt-1 rounded-full border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-main)] focus:ring-primary-dynamic focus:border-primary-dynamic py-2.5 px-4 text-sm font-medium" required>
                                <option value="legal" class="bg-[var(--bg-card)] text-[var(--text-main)]">Dependente Legal (R$ 80,00)</option>
                                <option value="nao_legal" class="bg-[var(--bg-card)] text-[var(--text-main)]">Não-Legal / Agregado (R$ 100,00)</option>
                            </select>
                            <InputError :message="depForm.errors.tipo_dependencia" />
                        </div>
                        <div class="sm:col-span-2">
                            <InputLabel for="email" value="E-mail do Dependente (Opcional)" class="!font-black text-[10px] uppercase tracking-widest" />
                            <TextInput id="email" v-model="depForm.email" type="email" class="w-full mt-1" placeholder="email@dependente.com" />
                            <InputError :message="depForm.errors.email" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="isDepModalOpen = false" type="button">
                            <XMarkIcon class="w-4 h-4 mr-2" /> Cancelar
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="depForm.processing">
                            <CheckIcon class="w-4 h-4 mr-2" /> {{ editingDep ? 'Atualizar Dados' : 'Gravar Dependente' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal Documento -->
        <Modal :show="isDocModalOpen" @close="isDocModalOpen = false">
            <div class="p-8">
                <h3 class="text-xl font-black text-[var(--text-heading)] tracking-tight mb-8">
                    Anexar Documento para Dependente
                </h3>
                <form @submit.prevent="submitDoc" class="space-y-6">
                    <div>
                        <InputLabel value="Título do Arquivo" class="!font-black text-[10px] uppercase tracking-widest" />
                        <TextInput v-model="docForm.titulo" type="text" class="w-full mt-1" placeholder="Ex: Certidão de Nascimento ou RG" required />
                        <InputError :message="docForm.errors.titulo" />
                    </div>
                    <div>
                        <InputLabel value="Tipo de Documento" class="!font-black text-[10px] uppercase tracking-widest" />
                        <select v-model="docForm.tipo" class="w-full mt-1 rounded-full border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-main)] focus:ring-primary-dynamic focus:border-primary-dynamic py-2.5 px-4 text-sm font-medium" required>
                            <option value="" class="bg-[var(--bg-card)]">Outros</option>
                            <option value="Identidade" class="bg-[var(--bg-card)] text-[var(--text-main)]">Identidade (RG/CNH)</option>
                            <option value="Certidão" class="bg-[var(--bg-card)] text-[var(--text-main)]">Certidão</option>
                            <option value="Comprovante Escola" class="bg-[var(--bg-card)] text-[var(--text-main)]">Comprovante Escolar</option>
                        </select>
                        <InputError :message="docForm.errors.tipo" />
                    </div>
                    <div>
                        <InputLabel value="Arquivo (MÁX 10MB)" class="!font-black text-[10px] uppercase tracking-widest mb-2" />
                        <FileInput v-model="docForm.arquivo" label="Clique para escolher o comprovante" id="arquivo_dep" />
                        <InputError :message="docForm.errors.arquivo" />
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="isDocModalOpen = false" type="button">
                            <XMarkIcon class="w-4 h-4 mr-2" /> Cancelar
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="docForm.processing">
                            <ArrowUpTrayIcon class="w-4 h-4 mr-2" /> Fazer Upload
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </CooperadoLayout>
</template>
