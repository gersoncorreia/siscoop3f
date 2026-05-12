<script setup>
import CooperadoLayout from '@/Layouts/CooperadoLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    BuildingOffice2Icon,
    MapPinIcon,
    BuildingStorefrontIcon,
    EyeIcon,
    ArrowRightIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    prestadores: Array
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
</script>

<template>
    <Head title="Rede de Prestadores" />

    <CooperadoLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">Rede Credenciada e Parceiros</h2>
                    <p class="text-[12px] text-[var(--text-muted)] mt-0.5 font-bold">
                        Encontre prestadores de saúde, serviços e produtos parceiros.
                    </p>
                </div>
                <div class="p-3 bg-[var(--bg-card)] rounded-xl border border-[var(--border-color)]">
                    <BuildingStorefrontIcon class="w-6 h-6 text-primary-dynamic" />
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Grade de Prestadores -->
            <div v-if="prestadores.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="prestador in prestadores" :key="prestador.id" class="zynix-card flex flex-col justify-between group hover:border-primary-dynamic/30 transition-all">
                    <div>
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 flex items-center justify-center shrink-0">
                                <BuildingOffice2Icon class="w-6 h-6" />
                            </div>
                            <span v-if="prestador.especialidades" class="text-[9px] font-black uppercase tracking-widest bg-primary-dynamic/10 text-primary-dynamic px-2.5 py-1 rounded-full text-center max-w-[120px] truncate">
                                {{ prestador.especialidades }}
                            </span>
                        </div>
                        
                        <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight mb-1">{{ prestador.nome }}</h3>
                        <p class="text-[10px] uppercase tracking-widest text-[var(--text-muted)] font-bold mb-4 line-clamp-1">{{ tipoLabel(prestador.tipo) }}</p>

                        <div class="space-y-2 mt-4">
                            <div v-if="prestador.endereco" class="flex items-start text-xs text-[var(--text-muted)] font-medium">
                                <MapPinIcon class="w-4 h-4 mr-2 text-slate-400 shrink-0 mt-0.5" /> 
                                <span class="line-clamp-2">{{ prestador.endereco }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Botão Ver Detalhes -->
                    <div class="mt-5 pt-4 border-t border-[var(--border-color)]">
                        <Link :href="route('cooperado.prestadores.show', prestador.id)" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-xs font-black uppercase tracking-widest bg-primary-dynamic/10 text-primary-dynamic hover:bg-primary-dynamic hover:text-white transition-all duration-300 group/btn">
                            <EyeIcon class="w-4 h-4" />
                            Ver Detalhes
                            <ArrowRightIcon class="w-3 h-3 opacity-0 -ml-2 group-hover/btn:opacity-100 group-hover/btn:ml-0 transition-all duration-300" />
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="zynix-card text-center py-16 border-dashed">
                <BuildingStorefrontIcon class="w-16 h-16 text-slate-300 mx-auto mb-4" />
                <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight mb-2">Nenhum Parceiro Disponível</h3>
                <p class="text-sm text-[var(--text-muted)] font-medium max-w-md mx-auto">
                    Ainda não há prestadores cadastrados na rede credenciada ou disponíveis para a sua região no momento.
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
