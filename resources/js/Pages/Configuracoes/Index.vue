<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { 
    PaintBrushIcon, 
    PhotoIcon, 
    CheckCircleIcon,
    SwatchIcon,
    VariableIcon,
    ArrowUpTrayIcon
} from '@heroicons/vue/24/outline';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { CheckBadgeIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    tenant: Object,
});

const form = useForm({
    name: props.tenant.name || props.tenant.id,
    primary_color: props.tenant.primary_color || '#2563eb',
    logo: null,
});

const submit = () => {
    form.post(route('configuracoes.update'), {
        forceFormData: true,
        onSuccess: () => {
            // Recarregar para aplicar mudanças globais se necessário
        }
    });
};

const onFileChange = (e) => {
    form.logo = e.target.files[0];
};
</script>

<template>
    <Head title="Configurações da Unidade" />

    <AuthenticatedLayout>
        <div class="py-6 sm:py-8 lg:py-12">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 lg:mb-12">
                    <div>
                        <Breadcrumbs :items="[{ label: 'Ajustes' }]" />
                        <h1 class="text-2xl lg:text-3xl font-black text-[var(--text-heading)] tracking-tighter mt-2">
                            Personalização da Unidade
                        </h1>
                    </div>
                </div>

                <div v-if="$page.props.flash?.success" class="mb-8 p-4 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20 rounded-2xl font-bold flex items-center gap-3 animate-in fade-in slide-in-from-top-4 duration-500">
                    <CheckBadgeIcon class="w-5 h-5" />
                    Configurações salvas com sucesso!
                </div>

                <div class="zynix-card overflow-hidden !p-0">
                    <div class="p-8 border-b border-[var(--border-color)] flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-primary-dynamic/10 text-primary-dynamic rounded-xl shadow-inner border border-primary-dynamic/20">
                                <PaintBrushIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="font-black text-[var(--text-heading)] leading-tight tracking-tight text-lg">Identidade Visual</h3>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mt-1">Sincronize a estética da sua cooperativa</p>
                            </div>
                        </div>
                        <SwatchIcon class="w-8 h-8 text-slate-200 dark:text-slate-700" />
                    </div>

                    <form @submit.prevent="submit" class="p-8 space-y-10">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                            <!-- Nome e Cor -->
                            <div class="space-y-8">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">Nome de Exibição</label>
                                    <input 
                                        v-model="form.name" 
                                        type="text" 
                                        class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-body)] focus:ring-primary-dynamic focus:border-primary-dynamic py-3.5 px-4 text-sm font-bold transition-all" 
                                        placeholder="Ex: Siscoop Saúde RS"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">Cor Primária do Sistema</label>
                                    <div class="flex items-center space-x-4">
                                        <div class="relative group">
                                            <input 
                                                v-model="form.primary_color" 
                                                type="color" 
                                                class="w-16 h-16 rounded-full border-none p-0 cursor-pointer overflow-hidden shadow-xl shadow-primary-dynamic/20 hover:scale-105 transition-transform"
                                            />
                                        </div>
                                        <div class="flex-1 relative">
                                            <input 
                                                v-model="form.primary_color" 
                                                type="text" 
                                                class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-body)] focus:ring-primary-dynamic focus:border-primary-dynamic py-3.5 px-4 font-mono text-sm font-black uppercase"
                                                placeholder="#000000"
                                            />
                                            <VariableIcon class="w-4 h-4 absolute right-4 top-1/2 -translate-y-1/2 text-slate-400" />
                                        </div>
                                    </div>
                                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 mt-3 italic flex items-center gap-2">
                                        <span class="w-1 h-1 rounded-full bg-primary-dynamic"></span>
                                        Esta cor será aplicada em botões, links e destaques.
                                    </p>
                                </div>
                            </div>

                            <!-- Logo -->
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">Logotipo da Unidade</label>
                                <div class="relative group">
                                    <div class="w-full aspect-video rounded-2xl border-2 border-dashed border-[var(--border-color)] flex flex-col items-center justify-center bg-slate-50/50 dark:bg-slate-900/30 overflow-hidden relative transition-colors group-hover:border-primary-dynamic/50">
                                        <img v-if="$page.props.tenant.logo_url && !form.logo" :src="$page.props.tenant.logo_url" class="max-w-[70%] max-h-[70%] object-contain drop-shadow-md" />
                                        <div v-else-if="form.logo" class="flex flex-col items-center animate-bounce">
                                            <CheckCircleIcon class="w-12 h-12 text-emerald-500 mb-2" />
                                            <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Novo Logo Pronto</span>
                                        </div>
                                        <PhotoIcon v-else class="w-12 h-12 text-slate-200 dark:text-slate-700" />
                                        
                                        <input 
                                            type="file" 
                                            @change="onFileChange" 
                                            class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                        />
                                        
                                        <div class="absolute inset-0 bg-primary-dynamic/0 group-hover:bg-primary-dynamic/5 flex items-center justify-center transition-all duration-300 pointer-events-none">
                                            <div class="opacity-0 group-hover:opacity-100 flex flex-col items-center transition-all duration-300 transform translate-y-4 group-hover:translate-y-0">
                                                <ArrowUpTrayIcon class="w-6 h-6 text-primary-dynamic mb-2" />
                                                <span class="text-[10px] font-black text-primary-dynamic uppercase tracking-widest">Alterar Imagem</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex items-center justify-between text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            PNG ou JPG (400x120px)
                                        </span>
                                        <span v-if="form.logo" class="text-emerald-500 flex items-center gap-1">
                                            <CheckBadgeIcon class="w-3 h-3" /> Arquivo Carregado
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div class="pt-8 border-t border-[var(--border-color)]">
                            <h4 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-5">Simulação em Tempo Real</h4>
                            <div class="p-8 bg-slate-50/50 dark:bg-slate-900/30 rounded-2xl border border-[var(--border-color)]/30 backdrop-blur-sm relative overflow-hidden">
                                <!-- Background accent -->
                                <div class="absolute top-0 right-0 w-32 h-32 bg-primary-dynamic/5 rounded-full blur-3xl -mr-16 -mt-16"></div>
                                
                                <div class="flex flex-col sm:flex-row items-center gap-6 relative z-10">
                                    <div class="flex-1 space-y-3">
                                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 italic leading-relaxed">
                                            As alterações de cor e logo são refletidas instantaneamente em todos os dashboards, botões, tabelas e comunicações por e-mail da cooperativa.
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-4 shrink-0">
                                        <button 
                                            type="button"
                                            :style="{ backgroundColor: form.primary_color }"
                                            class="px-6 py-3 rounded-full text-white text-[10px] font-black uppercase tracking-widest shadow-xl shadow-primary-dynamic/20 transition-all hover:scale-105"
                                        >
                                            Botão Primário
                                        </button>
                                        <span :style="{ color: form.primary_color }" class="text-[10px] font-black uppercase tracking-widest underline decoration-2 underline-offset-4 cursor-pointer hover:opacity-80 transition-opacity">Link de Destaque</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4">
                            <PrimaryButton 
                                type="submit" 
                                :disabled="form.processing"
                                class="w-full !py-6 !text-[11px] !tracking-[0.25em] !font-black !rounded-full shadow-2xl transition-all active:scale-[0.98]"
                            >
                                <CheckCircleIcon v-if="!form.processing" class="w-5 h-5 mr-3" />
                                <span v-else class="mr-3 animate-spin border-2 border-white/20 border-t-white rounded-full w-4 h-4"></span>
                                SALVAR IDENTIDADE VISUAL
                            </PrimaryButton>
                        </div>
                    </form>
                </div>

                <!-- Gestão de Acessos (Nova Seção) -->
                <div v-if="$page.props.auth.permissions.includes('configuracoes.users')" class="zynix-card overflow-hidden !p-0 mt-8 mb-12">
                    <div class="p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-indigo-500/5 border-b border-[var(--border-color)]">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-indigo-500/10 text-indigo-500 rounded-xl shadow-inner border border-indigo-500/20">
                                <ShieldCheckIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="font-black text-[var(--text-heading)] leading-tight tracking-tight text-lg">Controle de Acessos</h3>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mt-1">Gerencie quem pode acessar esta unidade</p>
                            </div>
                        </div>
                        <Link :href="route('configuracoes.usuarios.index')">
                            <PrimaryButton class="!bg-indigo-600 hover:!bg-indigo-700 !px-8 !py-3 !rounded-full !text-[10px] !font-black !uppercase !tracking-widest">
                                Gerenciar Equipe
                            </PrimaryButton>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
