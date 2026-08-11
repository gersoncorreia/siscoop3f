<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { 
    CheckIcon, 
    XMarkIcon, 
    EyeIcon, 
    UserIcon,
    ClockIcon,
    CalendarIcon,
    IdentificationIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    pendentes: Object
});

function aprovar(id) {
    router.post(route('associados.adesoes.aprovar', id));
}

function rejeitar(id) {
    router.post(route('associados.adesoes.rejeitar', id));
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('pt-BR');
}
</script>

<template>
    <Head title="Solicitações de Adesão" />

    <AuthenticatedLayout>
        <template #header>
            <Breadcrumbs :items="[{ label: 'Associados', href: '/associados' }, { label: 'Adesões' }]" />
            <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Solicitações de Adesão</h2>
            <p class="text-sm text-[var(--text-muted)] mt-0.5">Analise e aprove as pré-adesões enviadas pelo formulário público.</p>
        </template>

        <div class="space-y-6 pb-12">
                
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="zynix-card group hover:scale-[1.02] transition-all duration-300">
                        <div class="flex items-center gap-4">
                            <div class="p-4 rounded-2xl bg-amber-500/10 text-amber-500 shadow-inner group-hover:bg-amber-500/20 transition-colors">
                                <ClockIcon class="w-8 h-8" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Aguardando Análise</p>
                                <p class="text-2xl font-black text-[var(--text-heading)] tracking-tighter mt-1">{{ pendentes.total }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Table -->
                <div class="zynix-table-wrapper">
                    <div v-if="pendentes.data.length === 0" class="text-center py-20">
                        <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6">
                            <ClockIcon class="w-10 h-10 text-slate-300" />
                        </div>
                        <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">Nenhuma solicitação pendente</h3>
                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-2 max-w-xs mx-auto leading-relaxed">
                            Novas adesões aparecerão aqui conforme forem enviadas pelos clientes por meio do link público.
                        </p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="zynix-table w-full">
                            <thead>
                                <tr>
                                    <th class="pl-8">Associado</th>
                                    <th class="text-center">Documento</th>
                                    <th class="text-center">Solicitado em</th>
                                    <th class="text-right pr-8">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in pendentes.data" :key="item.id">
                                    <td class="pl-8">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-9 h-9 p-2 rounded-xl bg-primary-dynamic/10 text-primary-dynamic shadow-inner">
                                                <UserIcon class="w-full h-full" />
                                            </div>
                                            <div class="ml-4">
                                                <div class="font-black text-[var(--text-heading)] tracking-tight">{{ item.nome }}</div>
                                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-tight">{{ item.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="inline-flex items-center gap-2 font-mono text-xs text-slate-600 dark:text-slate-400">
                                            {{ item.cpf }}
                                        </div>
                                    </td>
                                    <td class="text-center font-medium">
                                        {{ formatDate(item.created_at) }}
                                    </td>
                                    <td class="text-right pr-8 whitespace-nowrap">
                                        <div class="flex items-center justify-end space-x-1">
                                            <button 
                                                @click="aprovar(item.id)" 
                                                class="p-2 text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-xl transition-all"
                                                title="Aprovar"
                                            >
                                                <CheckIcon class="w-4 h-4" />
                                            </button>
                                            <button 
                                                @click="rejeitar(item.id)" 
                                                class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-all"
                                                title="Rejeitar"
                                            >
                                                <XMarkIcon class="w-4 h-4" />
                                            </button>
                                            <Link 
                                                :href="route('associados.edit', item.id)"
                                                class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-xl transition-all"
                                                title="Ver Detalhes"
                                            >
                                                <EyeIcon class="w-4 h-4" />
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="pendentes.data.length > 0 && pendentes.links" class="p-6 border-t border-[var(--border-color)] flex items-center justify-between">
                         <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                             Mostrando {{ pendentes.from }}-{{ pendentes.to }} de {{ pendentes.total }}
                         </div>
                    </div>
                </div>
        </div>
    </AuthenticatedLayout>
</template>
