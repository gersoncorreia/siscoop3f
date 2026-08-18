<script setup>
import { Head, router } from '@inertiajs/vue3';
import { 
    IdentificationIcon, 
    CalendarDaysIcon, 
    UserIcon, 
    BuildingOfficeIcon,
    ArrowRightOnRectangleIcon,
    ShieldCheckIcon,
    QrCodeIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    dependente: Object,
    agendamentos: Array,
    tenant: Object,
});

const logout = () => {
    router.post(route('dependente.magic-link.logout'));
};
</script>

<template>
    <Head :title="`Painel do Dependente - ${dependente.nome}`" />

    <div class="min-h-screen bg-[var(--bg-main)] text-[var(--text-main)] font-sans pb-12">
        <!-- Header Topbar -->
        <header class="bg-white dark:bg-slate-900 border-b border-[var(--border-color)] sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-primary-dynamic/10 text-primary-dynamic rounded-2xl">
                        <BuildingOfficeIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <h1 class="text-base font-black text-[var(--text-heading)] leading-tight">{{ tenant?.nome || 'Fundação Santa Casa' }}</h1>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Portal Digital do Dependente</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden sm:block text-right">
                        <p class="text-xs font-black text-[var(--text-heading)]">{{ dependente.nome }}</p>
                        <p class="text-[10px] text-slate-400">Titular: {{ dependente.titular_nome }}</p>
                    </div>
                    <button 
                        @click="logout" 
                        class="px-4 py-2 bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-100 rounded-full text-xs font-black uppercase tracking-wider flex items-center gap-1.5 transition-all"
                    >
                        <ArrowRightOnRectangleIcon class="w-4 h-4" />
                        Sair
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content Container -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 space-y-8">
            <!-- Welcome Banner -->
            <div class="zynix-card p-6 md:p-8 bg-gradient-to-r from-primary-dynamic/10 via-[var(--bg-card)] to-[var(--bg-card)] relative overflow-hidden">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-primary-dynamic text-white font-black text-xl flex items-center justify-center shadow-lg shadow-primary-dynamic/30">
                            {{ dependente.nome.substring(0, 2).toUpperCase() }}
                        </div>
                        <div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700">Acesso Autorizado</span>
                            <h2 class="text-2xl font-black text-[var(--text-heading)] mt-1 tracking-tight">{{ dependente.nome }}</h2>
                            <p class="text-xs text-slate-500 font-medium">Dependente Vinculado ao Titular: <strong>{{ dependente.titular_nome }}</strong></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Digital Card & Appointments Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Digital Membership Card (1 Column) -->
                <div class="space-y-4">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                        <IdentificationIcon class="w-4 h-4 text-primary-dynamic" />
                        Carteirinha Digital da Santa Casa
                    </h3>

                    <!-- Visual Credit-Card Style Card -->
                    <div class="w-full bg-gradient-to-br from-primary-dynamic via-slate-800 to-slate-900 text-white p-6 rounded-3xl shadow-2xl relative overflow-hidden ring-1 ring-white/20">
                        <div class="flex items-center justify-between mb-6">
                            <span class="text-[10px] font-black uppercase tracking-[0.25em] text-white/80">Fundação Santa Casa</span>
                            <ShieldCheckIcon class="w-6 h-6 text-emerald-400" />
                        </div>

                        <div class="space-y-4 my-6">
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest text-white/60">Nome do Dependente</p>
                                <p class="text-lg font-black tracking-tight truncate">{{ dependente.nome }}</p>
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[9px] font-black uppercase tracking-widest text-white/60">Número da Carteirinha</p>
                                    <p class="text-sm font-mono font-bold tracking-widest">{{ dependente.carteirinha_numero }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-white/60">Validade</p>
                                    <p class="text-xs font-mono font-bold">{{ dependente.carteirinha_validade }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-white/10 flex items-center justify-between text-[10px] text-white/70 font-medium">
                            <span>Titular: {{ dependente.titular_nome }}</span>
                            <span class="uppercase font-bold">{{ dependente.tipo_dependencia === 'nao_legal' ? 'Agregado' : 'Dependente' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Appointments List (2 Columns) -->
                <div class="lg:col-span-2 space-y-4">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                        <CalendarDaysIcon class="w-4 h-4 text-primary-dynamic" />
                        Meus Agendamentos & Consultas
                    </h3>

                    <div class="zynix-card !p-0 overflow-hidden">
                        <div v-if="!agendamentos || agendamentos.length === 0" class="p-12 text-center">
                            <CalendarDaysIcon class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Nenhum agendamento registrado no momento.</p>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="zynix-table w-full">
                                <thead>
                                    <tr>
                                        <th class="pl-6">Data / Hora</th>
                                        <th>Especialidade / Serviço</th>
                                        <th>Prestador</th>
                                        <th class="text-right pr-6">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="ag in agendamentos" :key="ag.id">
                                        <td class="pl-6 font-mono text-xs font-bold text-[var(--text-heading)]">
                                            {{ ag.data_hora }}
                                        </td>
                                        <td>
                                            <p class="font-bold text-[var(--text-heading)] text-xs">{{ ag.servico_nome || ag.especialidade }}</p>
                                        </td>
                                        <td class="text-xs font-medium">
                                            {{ ag.prestador_nome }}
                                        </td>
                                        <td class="text-right pr-6">
                                            <span 
                                                :class="{
                                                    'bg-emerald-50 text-emerald-600 border-emerald-100': ag.status === 'realizado' || ag.status === 'confirmado',
                                                    'bg-amber-50 text-amber-600 border-amber-100': ag.status === 'pendente'
                                                }"
                                                class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider border rounded-full inline-flex items-center"
                                            >
                                                {{ ag.status }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
</style>
