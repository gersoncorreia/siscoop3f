<script setup>
import CooperadoLayout from '@/Layouts/CooperadoLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { Head } from '@inertiajs/vue3';
import { DocumentTextIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    historico: Array,
});
</script>

<template>
    <Head title="Meu Prontuário" />
    <CooperadoLayout>
        <template #header>
            <Breadcrumbs :items="[{ label: 'Prontuário Eletrônico' }]" />
            <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Prontuário Eletrônico</h2>
            <p class="text-sm text-[var(--text-muted)] mt-0.5">Seu histórico clínico e atendimentos realizados.</p>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Empty State -->
            <div v-if="historico.length === 0" class="zynix-card flex flex-col items-center justify-center py-16 text-center">
                <DocumentTextIcon class="w-12 h-12 text-slate-300 mb-4" />
                <h3 class="text-sm font-black text-[var(--text-heading)] uppercase tracking-widest mb-2">Histórico vazio</h3>
                <p class="text-xs text-slate-400 font-medium">Ainda não há registros de atendimentos no seu prontuário.</p>
            </div>

            <!-- Timeline -->
            <div v-else class="relative">
                <!-- Vertical line -->
                <div class="absolute left-5 top-0 bottom-0 w-0.5 bg-[var(--border-color)]"></div>

                <div class="space-y-6">
                    <div
                        v-for="(item, idx) in historico"
                        :key="item.id"
                        class="relative flex gap-5 pl-14"
                    >
                        <!-- Circle on timeline -->
                        <div class="absolute left-3 top-4 w-4 h-4 rounded-full bg-primary-dynamic border-2 border-[var(--bg-main)]"></div>

                        <!-- Card -->
                        <div class="zynix-card flex-1">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <p class="text-[9px] font-black text-primary-dynamic uppercase tracking-widest mb-1">
                                        {{ new Date(item.data_atendimento).toLocaleDateString('pt-BR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                                    </p>
                                    <h3 class="text-base font-black text-[var(--text-heading)]">{{ item.diagnostico_principal }}</h3>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 text-xs text-slate-400 font-medium mb-3">
                                <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded-md font-bold">Dr. {{ item.medico_nome }}</span>
                                <span class="text-slate-300">•</span>
                                <span>CRM: {{ item.crm }}</span>
                            </div>

                            <div v-if="item.prescricao" class="bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 rounded-xl p-3 mb-3">
                                <p class="text-[9px] font-black uppercase tracking-widest text-blue-500 mb-1">💊 Prescrição</p>
                                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">{{ item.prescricao }}</p>
                            </div>

                            <div v-if="item.observacoes_clinicas" class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-3">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">📝 Observações Clínicas</p>
                                <p class="text-xs text-slate-500 leading-relaxed">{{ item.observacoes_clinicas }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CooperadoLayout>
</template>
