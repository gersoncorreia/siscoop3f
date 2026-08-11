<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { Head, router } from '@inertiajs/vue3';
import { CheckCircleIcon, XCircleIcon, CalendarDaysIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    agendamentos: Object, // paginated
});

const statusColor = (status) => ({
    pendente:   'bg-amber-50 text-amber-600 dark:bg-amber-500/10',
    confirmado: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10',
    realizado:  'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10',
    cancelado:  'bg-rose-50 text-rose-600 dark:bg-rose-500/10',
});

const confirmar = (id) => router.post(route('saude.agendamentos.confirmar', id), {}, { preserveScroll: true });
const cancelar  = (id) => router.post(route('saude.agendamentos.cancelar', id), {}, { preserveScroll: true });
const realizar  = (id) => router.post(route('saude.agendamentos.realizar', id), {}, { preserveScroll: true });
</script>

<template>
    <Head title="Gestão de Agendamentos" />
    <AuthenticatedLayout>
        <template #header>
            <Breadcrumbs :items="[{ label: 'Clube de Saúde' }, { label: 'Agendamentos' }]" />
            <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Gestão de Agendamentos</h2>
            <p class="text-sm text-[var(--text-muted)] mt-0.5">Consultas e exames solicitados pelos clientes.</p>
        </template>

        <div class="pb-12">
            <div class="zynix-card !p-0 overflow-hidden">
                <div class="p-5 border-b border-[var(--border-color)]">
                    <h3 class="text-sm font-black text-[var(--text-heading)] uppercase tracking-widest">Todos os Agendamentos</h3>
                </div>

                <div v-if="agendamentos.data.length === 0" class="p-12 text-center">
                    <CalendarDaysIcon class="w-10 h-10 text-slate-300 mx-auto mb-3" />
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Nenhum agendamento registrado.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="zynix-table w-full">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Tipo / Especialidade</th>
                                <th>Prestador</th>
                                <th>Data / Hora</th>
                                <th>Status</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="ag in agendamentos.data" :key="ag.id">
                                <td class="font-bold text-sm">{{ ag.user?.name }}</td>
                                <td>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-xs">{{ ag.especialidade }}</span>
                                        <span class="text-[9px] text-slate-400 uppercase tracking-wider">{{ ag.tipo }}</span>
                                    </div>
                                </td>
                                <td class="text-xs text-slate-500">{{ ag.prestador?.nome || '—' }}</td>
                                <td class="text-xs font-bold text-slate-500">
                                    {{ new Date(ag.data_hora).toLocaleDateString('pt-BR') }}
                                    <span class="text-slate-400"> {{ new Date(ag.data_hora).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) }}</span>
                                </td>
                                <td>
                                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest" :class="statusColor(ag.status)">
                                        {{ ag.status }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <button v-if="ag.status === 'pendente'" @click="confirmar(ag.id)"
                                            class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-500/10 transition-all" title="Confirmar">
                                            <CheckCircleIcon class="w-4 h-4" />
                                        </button>
                                        <button v-if="ag.status === 'confirmado'" @click="realizar(ag.id)"
                                            class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-500/10 transition-all" title="Marcar como Realizado">
                                            ✅
                                        </button>
                                        <button v-if="['pendente','confirmado'].includes(ag.status)" @click="cancelar(ag.id)"
                                            class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-500/10 transition-all" title="Cancelar">
                                            <XCircleIcon class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
