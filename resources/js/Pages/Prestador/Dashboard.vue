<script setup>
import PrestadorLayout from '@/Layouts/PrestadorLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import {
    Squares2X2Icon,
    MagnifyingGlassIcon,
    CheckCircleIcon,
    XCircleIcon,
    ExclamationTriangleIcon,
    ClockIcon,
    ClipboardDocumentListIcon,
    UserIcon,
    TagIcon
} from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    prestador: Object,
    solicitacoes: Array,
    servicos: Array,
});

const page = usePage();
const primaryColor = computed(() => page.props.tenant?.primary_color || '#2563eb');

const form = useForm({
    identificacao: '',
    servico_id: '',
    observacoes: ''
});

const searchResults = ref([]);
const isSearching = ref(false);
const showDropdown = ref(false);
const searchDropdownRef = ref(null);

let searchTimeout;

watch(() => form.identificacao, (newVal) => {
    // Prevent search if selected from dropdown
    const selected = searchResults.value.find(s => s.cpf === newVal || s.matricula === newVal);
    if (selected) {
        showDropdown.value = false;
        return;
    }
    
    if (newVal.length < 3) {
        searchResults.value = [];
        showDropdown.value = false;
        return;
    }

    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        isSearching.value = true;
        try {
            const response = await axios.get(route('prestador.buscar-cooperado', { q: newVal }));
            searchResults.value = response.data;
            showDropdown.value = searchResults.value.length > 0;
        } catch (error) {
            console.error('Erro na busca', error);
        } finally {
            isSearching.value = false;
        }
    }, 400);
});

const selectCooperado = (cooperado) => {
    form.identificacao = cooperado.cpf; 
    searchResults.value = [];
    showDropdown.value = false;
};

const closeDropdown = (e) => {
    if (searchDropdownRef.value && !searchDropdownRef.value.contains(e.target)) {
        showDropdown.value = false;
    }
};

onMounted(() => document.addEventListener('click', closeDropdown));
onUnmounted(() => document.removeEventListener('click', closeDropdown));

const submitSolicitacao = () => {
    form.post(route('prestador.solicitar'), {
        preserveScroll: true,
        onSuccess: () => {
            if (page.props.flash?.success) {
                form.reset();
            }
        }
    });
};

const statusConfig = {
    'pendente_autorizacao': { color: 'text-amber-500', bg: 'bg-amber-50 dark:bg-amber-500/10', icon: ClockIcon, label: 'Aguardando Hélio / Coop' },
    'aprovado_cooperativa': { color: 'text-emerald-500', bg: 'bg-emerald-50 dark:bg-emerald-500/10', icon: CheckCircleIcon, label: 'Autorizado' },
    'realizado': { color: 'text-blue-500', bg: 'bg-blue-50 dark:bg-blue-500/10', icon: ClipboardDocumentListIcon, label: 'Realizado' },
    'negado': { color: 'text-rose-500', bg: 'bg-rose-50 dark:bg-rose-500/10', icon: XCircleIcon, label: 'Não Autorizado' }
};

const getStatus = (status) => statusConfig[status] || statusConfig['pendente_autorizacao'];

</script>

<template>
    <Head title="Painel do Parceiro" />

    <PrestadorLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">Painel de Atendimentos</h2>
                    <p class="text-[12px] text-[var(--text-muted)] mt-0.5 font-bold">
                        Inicie solicitações e acompanhe as autorizações da cooperativa.
                    </p>
                </div>
                <div class="p-3 bg-[var(--bg-card)] rounded-xl border border-[var(--border-color)]">
                    <Squares2X2Icon class="w-6 h-6 text-primary-dynamic" />
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pb-12">
            
            <!-- Coluna Esquerda: Nova Solicitação -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- Flash Messages -->
                <div v-if="$page.props.flash?.success" class="bg-emerald-50 text-emerald-700 p-4 rounded-xl border border-emerald-100 flex items-center">
                    <CheckCircleIcon class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span class="text-sm font-bold">{{ $page.props.flash.success }}</span>
                </div>
                
                <div v-if="$page.props.flash?.error === 'inadimplente'" class="bg-rose-50 border border-rose-200 p-5 rounded-xl">
                    <div class="flex items-start">
                        <div class="shrink-0 bg-rose-100 p-2 rounded-full mt-1">
                            <ExclamationTriangleIcon class="w-6 h-6 text-rose-600" />
                        </div>
                        <div class="ml-4">
                            <h3 class="text-base font-black text-rose-800">Cooperado com Restrições</h3>
                            <p class="text-sm text-rose-700 font-medium mt-1">
                                O sistema identificou pendências financeiras (Ex: Cota Capital em aberto). Por favor, oriente o cooperado a regularizar a situação pelo aplicativo ou entrar em contato com a cooperativa.
                            </p>
                        </div>
                    </div>
                </div>

                <div v-else-if="$page.props.flash?.error === 'cooperado_nao_encontrado'" class="bg-amber-50 text-amber-700 p-4 rounded-xl border border-amber-200 flex items-center">
                    <ExclamationTriangleIcon class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span class="text-sm font-bold">Cooperado não encontrado com este CPF ou Matrícula.</span>
                </div>

                <!-- Formulário de Novo Atendimento -->
                <div class="zynix-card p-6 border-t-4 border-t-primary-dynamic relative overflow-hidden group">
                    <div class="absolute -right-10 -top-10 text-slate-100 dark:text-slate-800/50 transform rotate-12 transition-transform duration-500 group-hover:scale-110 pointer-events-none">
                        <ClipboardDocumentListIcon class="w-48 h-48" />
                    </div>
                    
                    <h3 class="text-lg font-black text-[var(--text-heading)] mb-6 flex items-center relative z-10">
                        <UserIcon class="w-5 h-5 mr-2 text-primary-dynamic" /> Iniciar Novo Atendimento
                    </h3>

                    <form @submit.prevent="submitSolicitacao" class="space-y-5 relative z-10">
                        
                        <div>
                            <InputLabel for="identificacao" value="CPF ou Matrícula do Cooperado" class="!font-black text-[10px] uppercase tracking-widest" />
                            <div class="relative" ref="searchDropdownRef">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <MagnifyingGlassIcon v-if="!isSearching" class="h-4 w-4 text-slate-400" />
                                    <svg v-else class="animate-spin h-4 w-4 text-primary-dynamic" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                <TextInput 
                                    id="identificacao" 
                                    v-model="form.identificacao" 
                                    type="text" 
                                    class="mt-1 block w-full pl-10 transition-colors focus:border-primary-dynamic focus:ring-primary-dynamic" 
                                    placeholder="Digite o Nome, CPF ou Matrícula..."
                                    autocomplete="off"
                                    @focus="() => { if (searchResults.length > 0) showDropdown = true; }"
                                    required 
                                />

                                <!-- Autocomplete Dropdown -->
                                <div v-if="showDropdown && searchResults.length > 0" class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden max-h-60 overflow-y-auto">
                                    <ul class="py-1">
                                        <li v-for="coop in searchResults" :key="coop.id" @click="selectCooperado(coop)" class="px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer border-b border-slate-100 dark:border-slate-700/50 last:border-0 transition-colors">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm font-black text-slate-800 dark:text-slate-200">{{ coop.nome }}</p>
                                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-0.5">CPF: {{ coop.cpf || 'N/A' }}</p>
                                                </div>
                                                <div class="bg-primary-dynamic/10 text-primary-dynamic px-2 py-1 rounded text-[10px] font-black tracking-widest uppercase">
                                                    MAT: {{ coop.matricula }}
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <InputError :message="form.errors.identificacao" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="servico_id" value="Serviço ou Produto" class="!font-black text-[10px] uppercase tracking-widest" />
                            <div class="relative mt-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <TagIcon class="h-4 w-4 text-slate-400" />
                                </div>
                                <select 
                                    id="servico_id" 
                                    v-model="form.servico_id" 
                                    class="block w-full pl-10 text-sm rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-primary-dynamic focus:ring focus:ring-primary-dynamic/20"
                                    required
                                >
                                    <option value="" disabled selected>Selecione um item do seu catálogo</option>
                                    <option v-for="srv in servicos" :key="srv.id" :value="srv.id">
                                        {{ srv.nome }} {{ srv.valor_acordado ? `(R$ ${parseFloat(srv.valor_acordado).toFixed(2)})` : '' }}
                                    </option>
                                </select>
                            </div>
                            <InputError :message="form.errors.servico_id" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="observacoes" value="Observações Adicionais (Opcional)" class="!font-black text-[10px] uppercase tracking-widest" />
                            <textarea 
                                id="observacoes" 
                                v-model="form.observacoes" 
                                rows="3" 
                                class="mt-1 block w-full text-sm rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-primary-dynamic focus:ring focus:ring-primary-dynamic/20 resize-none" 
                                placeholder="Dente 45, Retorno, etc."
                            ></textarea>
                            <InputError :message="form.errors.observacoes" class="mt-2" />
                        </div>

                        <div class="pt-2">
                            <PrimaryButton class="w-full justify-center !py-4 shadow-lg shadow-primary-dynamic/20" :disabled="form.processing || servicos.length === 0">
                                {{ form.processing ? 'Verificando e Enviando...' : 'Solicitar Autorização' }}
                            </PrimaryButton>
                            <p v-if="servicos.length === 0" class="text-xs text-rose-500 mt-2 text-center font-bold">
                                Você precisa cadastrar serviços no catálogo primeiro.
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Coluna Direita: Últimas Solicitações -->
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-color)] overflow-hidden">
                    <div class="px-6 py-5 border-b border-[var(--border-color)] flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
                        <h3 class="text-base font-black text-[var(--text-heading)]">Atendimentos Recentes</h3>
                    </div>
                    
                    <div class="p-6">
                        <div v-if="solicitacoes.length > 0" class="space-y-4">
                            <div v-for="sol in solicitacoes" :key="sol.id" class="flex items-center justify-between p-4 rounded-xl border border-[var(--border-color)] bg-[var(--bg-app)] hover:border-primary-dynamic/30 transition-all">
                                <div class="flex items-center gap-4">
                                    <div :class="['w-10 h-10 rounded-full flex items-center justify-center shrink-0', getStatus(sol.status).bg, getStatus(sol.status).color]">
                                        <component :is="getStatus(sol.status).icon" class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-[var(--text-heading)]">{{ sol.paciente }}</p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[10px] text-[var(--text-muted)] font-bold uppercase tracking-widest bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-md">Mat: {{ sol.matricula }}</span>
                                            <span class="text-xs text-[var(--text-muted)] font-medium">{{ sol.servico }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right flex flex-col items-end">
                                    <span :class="['text-[10px] font-black uppercase tracking-widest', getStatus(sol.status).color]">
                                        {{ getStatus(sol.status).label }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-bold mt-1">{{ sol.data }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <Squares2X2Icon class="w-12 h-12 text-slate-200 mx-auto mb-3" />
                            <p class="text-sm text-[var(--text-muted)] font-bold">Nenhum atendimento iniciado ainda.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </PrestadorLayout>
</template>

<style scoped>
.text-primary-dynamic { color: v-bind(primaryColor); }
.bg-primary-dynamic { background-color: v-bind(primaryColor) !important; }
.border-primary-dynamic { border-color: v-bind(primaryColor) !important; }
</style>
