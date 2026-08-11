<script setup>
import CooperadoLayout from '@/Layouts/CooperadoLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    prestadores: Array,
    tabelaServicos: Array,
    selectedServico: Object,
});

function isExameCategoria(categoria) {
    if (!categoria) return false;
    const cat = String(categoria).toLowerCase();
    return cat.includes('exame') || cat.includes('laboratorio') || cat === 'exame_laboratorial' || cat === 'exame_imagem';
}

const activeServico = ref(props.selectedServico || null);
const isDropdownOpen = ref(false);
const searchQuery = ref(props.selectedServico ? props.selectedServico.nome : '');
const autocompleteContainer = ref(null);

const initialTipo = computed(() => {
    if (props.selectedServico) {
        return isExameCategoria(props.selectedServico.categoria) ? 'exame' : 'consulta';
    }
    return 'consulta';
});

const form = useForm({
    tipo: initialTipo.value,
    especialidade: props.selectedServico ? props.selectedServico.nome : '',
    tabela_servico_id: props.selectedServico ? props.selectedServico.id : '',
    prestador_id: '',
    data_hora: '',
    observacoes: '',
});

function cancelar() {
    form.reset();
    form.clearErrors();
    activeServico.value = null;
    searchQuery.value = '';
    router.visit(route('cooperado.agendamentos.index'));
}

// Click outside handler
function handleClickOutside(event) {
    if (autocompleteContainer.value && !autocompleteContainer.value.contains(event.target)) {
        isDropdownOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Filtrar serviços por Tipo (Consulta vs Exame) e depois por Query de busca
const filteredServicos = computed(() => {
    if (!props.tabelaServicos || !props.tabelaServicos.length) return [];
    
    // 1. Filtrar estritamente pelo tipo de atendimento selecionado
    let list = props.tabelaServicos.filter(s => {
        const isExame = isExameCategoria(s.categoria);
        return form.tipo === 'exame' ? isExame : !isExame;
    });

    // 2. Filtrar pelo texto digitado
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter(s => 
            s.nome.toLowerCase().includes(q) ||
            (s.codigo && s.codigo.toLowerCase().includes(q)) ||
            (s.categoria && s.categoria.toLowerCase().includes(q))
        );
    }

    return list.slice(0, 15);
});

function setTipo(novoTipo) {
    form.tipo = novoTipo;
    isDropdownOpen.value = true;
    
    // Se o serviço previamente selecionado for incompatível com o novo tipo, reseta
    if (activeServico.value) {
        const isExame = isExameCategoria(activeServico.value.categoria);
        if ((novoTipo === 'exame' && !isExame) || (novoTipo === 'consulta' && isExame)) {
            activeServico.value = null;
            form.tabela_servico_id = '';
            searchQuery.value = '';
            form.especialidade = '';
        }
    }
}

function selectServico(servico) {
    activeServico.value = servico;
    form.especialidade = servico.nome;
    form.tabela_servico_id = servico.id;
    searchQuery.value = servico.nome;
    isDropdownOpen.value = false;

    if (isExameCategoria(servico.categoria)) {
        form.tipo = 'exame';
    } else {
        form.tipo = 'consulta';
    }
}

function onSearchInput() {
    form.especialidade = searchQuery.value;
    form.tabela_servico_id = '';
    activeServico.value = null;
    isDropdownOpen.value = true;
}

const submitForm = () => {
    form.post(route('cooperado.agendamentos.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Novo Agendamento" />
    <CooperadoLayout>
        <template #header>
            <Breadcrumbs :items="[{ label: 'Agendamentos', href: route('cooperado.agendamentos.index') }, { label: 'Novo Agendamento' }]" />
            <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Solicitar Agendamento</h2>
            <p class="text-sm text-[var(--text-muted)] mt-0.5">Preencha as informações para solicitar seu agendamento.</p>
        </template>

        <div class="max-w-4xl pb-12">
            <div class="zynix-card">
                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Tipo de Atendimento -->
                    <div>
                        <InputLabel value="Tipo de Atendimento" class="!font-black tracking-widest uppercase text-[10px] mb-3" />
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                type="button"
                                @click="setTipo('consulta')"
                                class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 cursor-pointer transition-all"
                                :class="form.tipo === 'consulta' ? 'border-primary-dynamic bg-primary-light' : 'border-[var(--border-color)] hover:border-slate-300'"
                            >
                                <span class="text-2xl">🩺</span>
                                <span class="text-xs font-black text-[var(--text-heading)]">Consulta Médica</span>
                            </button>
                            <button
                                type="button"
                                @click="setTipo('exame')"
                                class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 cursor-pointer transition-all"
                                :class="form.tipo === 'exame' ? 'border-primary-dynamic bg-primary-light' : 'border-[var(--border-color)] hover:border-slate-300'"
                            >
                                <span class="text-2xl">🔬</span>
                                <span class="text-xs font-black text-[var(--text-heading)]">Exame / Laboratório</span>
                            </button>
                        </div>
                        <InputError :message="form.errors.tipo" class="mt-2" />
                    </div>

                    <!-- Autocomplete: Especialidade / Serviço -->
                    <div ref="autocompleteContainer" class="relative">
                        <InputLabel for="especialidade" value="Especialidade / Serviço" class="!font-black tracking-widest uppercase text-[10px]" />
                        <div class="relative mt-1">
                            <TextInput
                                id="especialidade"
                                type="text"
                                class="block w-full pr-10"
                                v-model="searchQuery"
                                @input="onSearchInput"
                                @focus="isDropdownOpen = true"
                                :placeholder="form.tipo === 'exame' ? 'Buscar exame (ex: Hemograma, Ultrassom, Tomografia)...' : 'Buscar consulta (ex: Cardiologia, Clínico Geral, Dermatologia)...'"
                                required
                                autocomplete="off"
                            />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                <MagnifyingGlassIcon class="w-4 h-4" />
                            </div>
                        </div>

                        <!-- Dropdown Autocomplete com Transição Suave -->
                        <Transition
                            enter-active-class="transition ease-out duration-150"
                            enter-from-class="opacity-0 scale-95 -translate-y-1"
                            enter-to-class="opacity-100 scale-100 translate-y-0"
                            leave-active-class="transition ease-in duration-100"
                            leave-from-class="opacity-100 scale-100 translate-y-0"
                            leave-to-class="opacity-0 scale-95 -translate-y-1"
                        >
                            <div
                                v-if="isDropdownOpen && filteredServicos.length"
                                class="absolute z-50 left-0 right-0 mt-1 bg-[var(--bg-card)] border border-[var(--border-color)] rounded-xl shadow-2xl max-h-60 overflow-y-auto divide-y divide-[var(--border-color)]"
                            >
                                <button
                                    v-for="s in filteredServicos"
                                    :key="s.id"
                                    type="button"
                                    @click="selectServico(s)"
                                    class="w-full text-left p-3 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors flex items-center justify-between gap-3 group"
                                >
                                    <div>
                                        <p class="text-xs font-black text-[var(--text-heading)] group-hover:text-primary-dynamic transition-colors">{{ s.nome }}</p>
                                        <p class="text-[9px] font-bold text-[var(--text-muted)] uppercase tracking-wider">
                                            Código: {{ s.codigo || 'S/C' }} • {{ s.categoria }}
                                        </p>
                                    </div>
                                    <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 shrink-0">
                                        R$ {{ (s.valor_base || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                                    </span>
                                </button>
                            </div>
                        </Transition>
                        <InputError :message="form.errors.especialidade" class="mt-2" />
                    </div>

                    <!-- Prestador -->
                    <div>
                        <InputLabel for="prestador_id" value="Clínica / Prestador (opcional)" class="!font-black tracking-widest uppercase text-[10px]" />
                        <select
                            id="prestador_id"
                            v-model="form.prestador_id"
                            class="block w-full border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 rounded-xl text-xs font-bold text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic transition-all py-2.5"
                        >
                            <option value="">Sem preferência</option>
                            <option v-for="p in prestadores" :key="p.id" :value="p.id">{{ p.nome }} — {{ p.especialidade }}</option>
                        </select>
                        <InputError :message="form.errors.prestador_id" class="mt-2" />
                    </div>

                    <!-- Data e Hora -->
                    <div>
                        <InputLabel for="data_hora" value="Data e Horário Desejado" class="!font-black tracking-widest uppercase text-[10px]" />
                        <TextInput
                            id="data_hora"
                            type="datetime-local"
                            class="block w-full"
                            v-model="form.data_hora"
                            required
                        />
                        <InputError :message="form.errors.data_hora" class="mt-2" />
                    </div>

                    <!-- Observações -->
                    <div>
                        <InputLabel for="observacoes" value="Observações (opcional)" class="!font-black tracking-widest uppercase text-[10px]" />
                        <textarea
                            id="observacoes"
                            v-model="form.observacoes"
                            rows="3"
                            placeholder="Alguma informação adicional para o atendimento?"
                            class="block w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-xs font-medium text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic transition-all p-3 resize-none"
                        ></textarea>
                        <InputError :message="form.errors.observacoes" class="mt-2" />
                    </div>

                    <!-- Card de Resumo de Valor e Serviço (Posicionado no Rodapé) -->
                    <div v-if="activeServico" class="p-4 rounded-xl border-2 border-emerald-500/30 bg-emerald-50/50 dark:bg-emerald-500/10 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black shrink-0">
                                R$
                            </div>
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest text-emerald-700 dark:text-emerald-400">Resumo do Serviço Selecionado</p>
                                <p class="text-sm font-black text-[var(--text-heading)] mt-0.5">{{ activeServico.nome }}</p>
                                <p class="text-[10px] font-bold text-[var(--text-muted)] uppercase">Código: {{ activeServico.codigo || 'S/C' }}</p>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">Valor de Referência</p>
                            <p class="text-xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">R$ {{ (activeServico.valor_base || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-[var(--border-color)]">
                        <SecondaryButton type="button" @click="cancelar">
                            Cancelar
                        </SecondaryButton>
                        <PrimaryButton :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                            Solicitar Agendamento
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </CooperadoLayout>
</template>
