<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import CooperadoLayout from '@/Layouts/CooperadoLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { 
    CurrencyDollarIcon,
    TagIcon,
    MagnifyingGlassIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    servicos: { type: Array, default: () => [] },
    categorias: { type: Object, default: () => ({}) },
    filtroCategoria: { type: String, default: null }
});

const page = usePage();
const tenant = computed(() => page.props.tenant);
const isFundacao = computed(() => tenant.value?.tipo === 'fundacao');
const routePrefix = computed(() => isFundacao.value ? '/cliente' : '/cooperado');

const activeFilter = ref(props.filtroCategoria || 'all');
const searchQuery = ref('');

const filteredServicos = computed(() => {
    let result = props.servicos;
    
    if (activeFilter.value !== 'all') {
        result = result.filter(s => s.categoria === activeFilter.value);
    }
    
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        result = result.filter(s => 
            s.nome.toLowerCase().includes(q) || 
            (s.codigo && s.codigo.toLowerCase().includes(q)) ||
            (s.descricao && s.descricao.toLowerCase().includes(q))
        );
    }
    
    return result;
});

function formatCurrency(v) {
    return (v || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}
</script>

<template>
    <Head title="Tabela de Serviços" />

    <CooperadoLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-primary-light flex items-center justify-center text-primary-dynamic">
                        <CurrencyDollarIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">Tabela de Serviços</h2>
                        <p class="text-[12px] text-[var(--text-muted)] mt-0.5 font-bold">
                            Consulte os valores de referência dos procedimentos e serviços.
                        </p>
                    </div>
                </div>
                
                <div class="relative max-w-sm w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <MagnifyingGlassIcon class="w-4 h-4 text-slate-400" />
                    </div>
                    <input 
                        v-model="searchQuery" 
                        type="text" 
                        placeholder="Buscar serviço por nome ou código..."
                        class="w-full pl-9 pr-4 py-2 bg-[var(--bg-card)] border border-[var(--border-color)] rounded-xl text-sm text-[var(--text-heading)] focus:ring-2 focus:ring-primary-dynamic focus:border-primary-dynamic transition-all placeholder:text-slate-400 font-medium"
                    >
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Filters -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                <button 
                    @click="activeFilter = 'all'"
                    class="px-4 py-2 rounded-xl text-[11px] font-black uppercase tracking-widest whitespace-nowrap transition-all border-2"
                    :class="activeFilter === 'all' 
                        ? 'bg-primary-dynamic border-primary-dynamic text-white shadow-md' 
                        : 'bg-transparent border-[var(--border-color)] text-[var(--text-muted)] hover:border-slate-300'"
                >
                    Todos os Serviços
                </button>
                <button 
                    v-for="(label, key) in categorias" :key="key"
                    @click="activeFilter = key"
                    class="px-4 py-2 rounded-xl text-[11px] font-black uppercase tracking-widest whitespace-nowrap transition-all border-2"
                    :class="activeFilter === key 
                        ? 'bg-primary-dynamic border-primary-dynamic text-white shadow-md' 
                        : 'bg-transparent border-[var(--border-color)] text-[var(--text-muted)] hover:border-slate-300'"
                >
                    {{ label }}
                </button>
            </div>

            <!-- List -->
            <div class="zynix-card p-0 overflow-hidden">
                <div v-if="filteredServicos.length" class="divide-y divide-[var(--border-color)]">
                    <div v-for="servico in filteredServicos" :key="servico.id" class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-6 hover:bg-slate-50 dark:hover:bg-white/[0.02] transition-colors">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 text-[9px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 rounded-md">
                                    {{ servico.codigo || 'S/C' }}
                                </span>
                                <span class="px-2 py-1 bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 text-[9px] font-black uppercase tracking-widest rounded-md">
                                    {{ servico.categoria_label || servico.categoria }}
                                </span>
                            </div>
                            <h3 class="text-base font-black text-[var(--text-heading)] mb-1">{{ servico.nome }}</h3>
                            <p v-if="servico.descricao" class="text-xs font-medium text-[var(--text-muted)] max-w-2xl line-clamp-2">
                                {{ servico.descricao }}
                            </p>
                        </div>
                        
                        <div class="flex items-center gap-6 shrink-0 border-t md:border-t-0 pt-4 md:pt-0 border-[var(--border-color)]">
                            <div class="text-right">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-0.5">Valor Base</p>
                                <p class="text-xl font-black text-emerald-600 dark:text-emerald-500 tracking-tight">{{ formatCurrency(servico.valor_base) }}</p>
                            </div>
                            <Link :href="`${routePrefix}/agendamentos/novo?servico_id=${servico.id}`" class="inline-flex">
                                <PrimaryButton>Agendar</PrimaryButton>
                            </Link>
                        </div>
                    </div>
                </div>
                
                <!-- Empty State -->
                <div v-else class="py-16 px-4 text-center">
                    <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <TagIcon class="w-8 h-8 text-slate-400" />
                    </div>
                    <h3 class="text-base font-black text-[var(--text-heading)] mb-1">Nenhum serviço encontrado</h3>
                    <p class="text-sm font-medium text-[var(--text-muted)]">
                        Tente ajustar seus filtros de busca ou selecione outra categoria.
                    </p>
                    <button @click="activeFilter = 'all'; searchQuery = ''" class="mt-4 text-[11px] font-black uppercase tracking-widest text-primary-dynamic hover:underline">
                        Limpar Filtros
                    </button>
                </div>
            </div>
        </div>
    </CooperadoLayout>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar {
    display: none;
}
</style>
