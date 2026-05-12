<script setup>
import CooperadoLayout from '@/Layouts/CooperadoLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    BuildingOffice2Icon,
    MapPinIcon,
    TagIcon,
    ClockIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClipboardDocumentListIcon,
    ArrowLeftIcon,
    CurrencyDollarIcon,
    ChatBubbleBottomCenterTextIcon,
    ExclamationTriangleIcon,
    SparklesIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    prestador: Object,
    servicos: Array,
    solicitacoes: Array,
});

const page = usePage();
const primaryColor = computed(() => page.props.tenant?.primary_color || '#2563eb');

const tipoLabel = (tipo) => {
    const map = {
        'clinica': 'Clínica',
        'hospital': 'Hospital',
        'medico_pf': 'Médico(a)',
        'laboratorio': 'Laboratório',
        'farmacia': 'Farmácia',
        'otica': 'Ótica',
    };
    return map[tipo] || tipo;
};

const statusConfig = {
    'pendente_autorizacao': { color: 'text-amber-500', bg: 'bg-amber-50 dark:bg-amber-500/10', border: 'border-amber-200 dark:border-amber-500/20', icon: ClockIcon, label: 'Aguardando Aprovação' },
    'aprovado_cooperativa': { color: 'text-emerald-500', bg: 'bg-emerald-50 dark:bg-emerald-500/10', border: 'border-emerald-200 dark:border-emerald-500/20', icon: CheckCircleIcon, label: 'Aprovado' },
    'realizado': { color: 'text-blue-500', bg: 'bg-blue-50 dark:bg-blue-500/10', border: 'border-blue-200 dark:border-blue-500/20', icon: ClipboardDocumentListIcon, label: 'Realizado' },
    'negado': { color: 'text-rose-500', bg: 'bg-rose-50 dark:bg-rose-500/10', border: 'border-rose-200 dark:border-rose-500/20', icon: XCircleIcon, label: 'Negado' },
};

const getStatus = (status) => statusConfig[status] || statusConfig['pendente_autorizacao'];

const formatCurrency = (val) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val || 0);
};
</script>

<template>
    <Head :title="`Detalhes - ${prestador.nome}`" />

    <CooperadoLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <Link :href="route('cooperado.prestadores')" class="inline-flex items-center gap-1.5 text-[10px] font-black text-primary-dynamic uppercase tracking-widest mb-3 hover:opacity-70 transition-opacity">
                        <ArrowLeftIcon class="w-3 h-3" />
                        Voltar para Rede Credenciada
                    </Link>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">{{ prestador.nome }}</h2>
                    <p class="text-[12px] text-[var(--text-muted)] mt-0.5 font-bold">
                        Detalhes do parceiro, serviços disponíveis e seu histórico de solicitações.
                    </p>
                </div>
                <div class="p-3 bg-[var(--bg-card)] rounded-xl border border-[var(--border-color)]">
                    <BuildingOffice2Icon class="w-6 h-6 text-primary-dynamic" />
                </div>
            </div>
        </template>

        <div class="space-y-8 pb-12">

            <!-- Informações do Prestador -->
            <div class="zynix-card p-6">
                <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                    <div class="w-16 h-16 rounded-2xl bg-primary-dynamic/10 text-primary-dynamic flex items-center justify-center shrink-0">
                        <BuildingOffice2Icon class="w-8 h-8" />
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                            <h3 class="text-xl font-black text-[var(--text-heading)] tracking-tight">{{ prestador.nome }}</h3>
                            <span class="text-[9px] font-black uppercase tracking-widest bg-slate-100 dark:bg-slate-800 text-slate-500 px-2.5 py-1 rounded-full">
                                {{ tipoLabel(prestador.tipo) }}
                            </span>
                        </div>
                        <div v-if="prestador.endereco" class="flex items-center text-sm text-[var(--text-muted)] font-medium mb-2">
                            <MapPinIcon class="w-4 h-4 mr-2 text-slate-400 shrink-0" />
                            {{ prestador.endereco }}
                        </div>
                        <div v-if="prestador.especialidades.length > 0" class="flex flex-wrap gap-2 mt-3">
                            <span v-for="esp in prestador.especialidades" :key="esp" class="text-[9px] font-black uppercase tracking-widest bg-primary-dynamic/10 text-primary-dynamic px-2.5 py-1 rounded-full">
                                {{ esp }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Serviços Disponíveis -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <SparklesIcon class="w-5 h-5 text-primary-dynamic" />
                    <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">Serviços e Produtos Disponíveis</h3>
                </div>

                <div v-if="servicos.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="srv in servicos" :key="srv.id" class="zynix-card p-5 hover:border-primary-dynamic/30 transition-all">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-primary-dynamic/10 text-primary-dynamic flex items-center justify-center">
                                    <TagIcon class="w-4 h-4" />
                                </div>
                                <h4 class="text-sm font-black text-[var(--text-heading)] tracking-tight">{{ srv.nome }}</h4>
                            </div>
                        </div>
                        <p v-if="srv.descricao" class="text-xs text-[var(--text-muted)] font-medium mt-2 line-clamp-2">{{ srv.descricao }}</p>
                        <div v-if="srv.valor_acordado > 0" class="mt-3 pt-3 border-t border-[var(--border-color)]">
                            <span class="text-sm font-black text-primary-dynamic">{{ formatCurrency(srv.valor_acordado) }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="zynix-card p-8 text-center border-dashed">
                    <TagIcon class="w-10 h-10 text-slate-300 mx-auto mb-3" />
                    <p class="text-sm text-[var(--text-muted)] font-bold">Este parceiro ainda não cadastrou serviços/produtos.</p>
                </div>
            </div>

            <!-- Minhas Solicitações neste Prestador -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <ClipboardDocumentListIcon class="w-5 h-5 text-primary-dynamic" />
                    <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">Minhas Solicitações neste Parceiro</h3>
                </div>

                <div v-if="solicitacoes.length > 0" class="space-y-4">
                    <div v-for="sol in solicitacoes" :key="sol.id" :class="['zynix-card p-5 border-l-4 transition-all hover:shadow-md', getStatus(sol.status).border]">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div :class="['w-10 h-10 rounded-full flex items-center justify-center shrink-0', getStatus(sol.status).bg, getStatus(sol.status).color]">
                                    <component :is="getStatus(sol.status).icon" class="w-5 h-5" />
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-[var(--text-heading)] tracking-tight">{{ sol.servico_produto }}</h4>
                                    <div class="flex flex-wrap items-center gap-3 mt-1">
                                        <span :class="['text-[10px] font-black uppercase tracking-widest', getStatus(sol.status).color]">
                                            {{ getStatus(sol.status).label }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-bold">{{ sol.data }}</span>
                                    </div>
                                    <div v-if="sol.observacoes" class="flex items-start gap-1.5 mt-2">
                                        <ChatBubbleBottomCenterTextIcon class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5" />
                                        <p class="text-xs text-[var(--text-muted)] font-medium italic">{{ sol.observacoes }}</p>
                                    </div>
                                    <div v-if="sol.motivo_recusa" class="flex items-start gap-1.5 mt-2 bg-rose-50 dark:bg-rose-500/10 p-2 rounded-lg">
                                        <ExclamationTriangleIcon class="w-3.5 h-3.5 text-rose-500 shrink-0 mt-0.5" />
                                        <p class="text-xs text-rose-600 dark:text-rose-400 font-bold">{{ sol.motivo_recusa }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="sol.valor > 0" class="text-right">
                                <div class="flex items-center gap-1.5 text-sm font-black text-[var(--text-heading)]">
                                    <CurrencyDollarIcon class="w-4 h-4 text-slate-400" />
                                    {{ formatCurrency(sol.valor) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="zynix-card p-8 text-center border-dashed">
                    <ClipboardDocumentListIcon class="w-10 h-10 text-slate-300 mx-auto mb-3" />
                    <p class="text-sm text-[var(--text-muted)] font-bold">Você ainda não possui solicitações com este parceiro.</p>
                    <p class="text-xs text-slate-400 mt-1 font-medium">
                        As solicitações são iniciadas pelo prestador quando você utilizar algum serviço.
                    </p>
                </div>
            </div>

        </div>
    </CooperadoLayout>
</template>

<style scoped>
.text-primary-dynamic { color: v-bind(primaryColor); }
.bg-primary-dynamic { background-color: v-bind(primaryColor) !important; }
.border-primary-dynamic { border-color: v-bind(primaryColor) !important; }
</style>
