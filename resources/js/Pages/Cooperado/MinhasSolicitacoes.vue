<script setup>
import CooperadoLayout from '@/Layouts/CooperadoLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    ClipboardDocumentListIcon,
    ClockIcon,
    CheckCircleIcon,
    XCircleIcon,
    BuildingStorefrontIcon,
    DocumentTextIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    solicitacoes: Array
});

const page = usePage();
const primaryColor = computed(() => page.props.tenant?.primary_color || '#2563eb');

const statusConfig = {
    'pendente': { color: 'text-amber-500', bg: 'bg-amber-50 dark:bg-amber-500/10', border: 'border-amber-200', icon: ClockIcon, label: 'Aguardando Cooperativa' },
    'pendente_autorizacao': { color: 'text-amber-500', bg: 'bg-amber-50 dark:bg-amber-500/10', border: 'border-amber-200', icon: ClockIcon, label: 'Aguardando Cooperativa' },
    'autorizado': { color: 'text-emerald-500', bg: 'bg-emerald-50 dark:bg-emerald-500/10', border: 'border-emerald-200', icon: CheckCircleIcon, label: 'Autorizado' },
    'aprovado_cooperativa': { color: 'text-emerald-500', bg: 'bg-emerald-50 dark:bg-emerald-500/10', border: 'border-emerald-200', icon: CheckCircleIcon, label: 'Autorizado' },
    'realizado': { color: 'text-blue-500', bg: 'bg-blue-50 dark:bg-blue-500/10', border: 'border-blue-200', icon: ClipboardDocumentListIcon, label: 'Realizado / Entregue' },
    'concluido': { color: 'text-blue-500', bg: 'bg-blue-50 dark:bg-blue-500/10', border: 'border-blue-200', icon: ClipboardDocumentListIcon, label: 'Concluído' },
    'negado': { color: 'text-rose-500', bg: 'bg-rose-50 dark:bg-rose-500/10', border: 'border-rose-200', icon: XCircleIcon, label: 'Não Autorizado' },
    'recusado': { color: 'text-rose-500', bg: 'bg-rose-50 dark:bg-rose-500/10', border: 'border-rose-200', icon: XCircleIcon, label: 'Não Autorizado' }
};

const getStatus = (status) => statusConfig[status] || statusConfig['pendente'];

</script>

<template>
    <Head title="Minhas Solicitações e Pedidos" />

    <CooperadoLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">Meus Pedidos e Consultas</h2>
                    <p class="text-[12px] text-[var(--text-muted)] mt-0.5 font-bold">
                        Acompanhe o status das solicitações abertas pelos prestadores e parceiros.
                    </p>
                </div>
                <div class="p-3 bg-[var(--bg-card)] rounded-xl border border-[var(--border-color)]">
                    <ClipboardDocumentListIcon class="w-6 h-6 text-primary-dynamic" />
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <div v-if="solicitacoes.length > 0" class="flex flex-col gap-4">
                <div v-for="solicitacao in solicitacoes" :key="solicitacao.id" class="zynix-card p-5 hover:border-primary-dynamic/30 transition-all flex flex-col sm:flex-row gap-5 items-start sm:items-center">
                    
                    <!-- Icon based on status -->
                    <div :class="['w-12 h-12 rounded-xl flex items-center justify-center shrink-0 border', getStatus(solicitacao.status).bg, getStatus(solicitacao.status).border, getStatus(solicitacao.status).color]">
                        <component :is="getStatus(solicitacao.status).icon" class="w-6 h-6" />
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span :class="['text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-full', getStatus(solicitacao.status).bg, getStatus(solicitacao.status).color]">
                                {{ getStatus(solicitacao.status).label }}
                            </span>
                            <span class="text-xs text-[var(--text-muted)] font-medium">• {{ solicitacao.data }}</span>
                        </div>
                        <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight mb-1 truncate">
                            {{ solicitacao.servico_produto }}
                        </h3>
                        <div class="flex items-center text-sm text-[var(--text-muted)] font-medium">
                            <BuildingStorefrontIcon class="w-4 h-4 mr-1.5 text-slate-400 shrink-0" />
                            <span class="truncate">{{ solicitacao.prestador }}</span>
                        </div>
                        
                        <div v-if="solicitacao.observacoes" class="mt-3 text-xs bg-slate-50 dark:bg-slate-800/50 p-3 rounded-lg border border-[var(--border-color)] text-[var(--text-muted)]">
                            <span class="font-bold block mb-1">Observações do Parceiro:</span>
                            {{ solicitacao.observacoes }}
                        </div>
                        
                        <div v-if="solicitacao.status === 'negado' || solicitacao.status === 'recusado'" class="mt-3 text-xs bg-rose-50 text-rose-700 p-3 rounded-lg border border-rose-100">
                            <span class="font-bold block mb-1">Motivo da Recusa:</span>
                            {{ solicitacao.motivo_recusa || 'Entre em contato com a cooperativa para mais detalhes.' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="zynix-card text-center py-16 border-dashed">
                <DocumentTextIcon class="w-16 h-16 text-slate-300 mx-auto mb-4" />
                <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight mb-2">Nenhum Pedido Registrado</h3>
                <p class="text-sm text-[var(--text-muted)] font-medium max-w-md mx-auto">
                    Você ainda não possui nenhum registro de atendimento ou compra. Quando você utilizar um serviço ou produto da rede credenciada, a solicitação aparecerá aqui.
                </p>
            </div>
        </div>
    </CooperadoLayout>
</template>

<style scoped>
.text-primary-dynamic { color: v-bind(primaryColor); }
.bg-primary-dynamic { background-color: v-bind(primaryColor) !important; }
.border-primary-dynamic { border-color: v-bind(primaryColor) !important; }
</style>
