<script setup>
import CooperadoLayout from '@/Layouts/CooperadoLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { Head, Link } from '@inertiajs/vue3';
import { CalendarDaysIcon, ClockIcon, CheckCircleIcon, XCircleIcon, PlusIcon } from '@heroicons/vue/24/outline';
import { computed } from 'vue';

const props = defineProps({
    agendamentos: Array,
});

const statusColor = (status) => ({
    pendente:   'bg-amber-50 text-amber-600 dark:bg-amber-500/10',
    confirmado: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10',
    realizado:  'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10',
    cancelado:  'bg-rose-50 text-rose-600 dark:bg-rose-500/10',
});

const tipoLabel = (tipo) => tipo === 'consulta' ? '🩺 Consulta' : '🔬 Exame';
</script>

<template>
    <Head title="Meus Agendamentos" />
    <CooperadoLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <Breadcrumbs :items="[{ label: 'Agendamentos' }]" />
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Meus Agendamentos</h2>
                    <p class="text-sm text-[var(--text-muted)] mt-0.5">Acompanhe consultas e exames solicitados.</p>
                </div>
                <Link :href="route('cooperado.agendamentos.create')" class="btn-primary flex items-center gap-2">
                    <PlusIcon class="w-4 h-4" />
                    Novo Agendamento
                </Link>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Empty State -->
            <div v-if="agendamentos.length === 0" class="zynix-card flex flex-col items-center justify-center py-16 text-center">
                <CalendarDaysIcon class="w-12 h-12 text-slate-300 mb-4" />
                <h3 class="text-sm font-black text-[var(--text-heading)] uppercase tracking-widest mb-2">Nenhum agendamento</h3>
                <p class="text-xs text-slate-400 font-medium mb-6">Você ainda não possui consultas ou exames agendados.</p>
                <Link :href="route('cooperado.agendamentos.create')" class="btn-primary flex items-center gap-2">
                    <PlusIcon class="w-4 h-4" />
                    Agendar Agora
                </Link>
            </div>

            <!-- Grid of cards -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                <div
                    v-for="ag in agendamentos"
                    :key="ag.id"
                    class="zynix-card relative overflow-hidden"
                >
                    <!-- Type Badge -->
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-black uppercase tracking-widest text-primary-dynamic">{{ tipoLabel(ag.tipo) }}</span>
                        <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest" :class="statusColor(ag.status)">
                            {{ ag.status }}
                        </span>
                    </div>

                    <h3 class="text-base font-black text-[var(--text-heading)] leading-tight mb-1">
                        {{ ag.tabela_servico?.nome || ag.especialidade }}
                    </h3>
                    <p v-if="ag.tabela_servico" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Especialidade: {{ ag.especialidade }}</p>
                    <p v-if="ag.prestador" class="text-xs font-bold text-slate-400 mb-3">Prestador: {{ ag.prestador.nome }}</p>

                    <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mt-3">
                        <CalendarDaysIcon class="w-4 h-4" />
                        {{ new Date(ag.data_hora).toLocaleDateString('pt-BR') }}
                        <ClockIcon class="w-4 h-4 ml-2" />
                        {{ new Date(ag.data_hora).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) }}
                    </div>

                    <!-- Payment Details Badge -->
                    <div class="mt-3 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-[var(--border-color)] flex items-center justify-between">
                        <div>
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 block">Valor do Serviço</span>
                            <span class="text-xs font-black text-[var(--text-heading)] font-mono">
                                {{ (ag.valor_cobrado || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) }}
                            </span>
                        </div>
                        <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-primary-dynamic/10 text-primary-dynamic uppercase tracking-wider">
                            {{ ag.status_pagamento_label }}
                        </span>
                    </div>

                    <p v-if="ag.observacoes" class="mt-3 text-[10px] text-slate-400 italic leading-relaxed border-t border-[var(--border-color)] pt-2">
                        {{ ag.observacoes }}
                    </p>

                    <!-- Cancel action -->
                    <div v-if="ag.status === 'pendente'" class="mt-4 pt-3 border-t border-[var(--border-color)]">
                        <Link
                            :href="route('cooperado.agendamentos.destroy', ag.id)"
                            method="delete"
                            as="button"
                            class="text-[10px] font-black uppercase tracking-widest text-rose-500 hover:text-rose-700 transition-colors"
                        >
                            Cancelar solicitação
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </CooperadoLayout>
</template>
