<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    ExclamationTriangleIcon, 
    ShieldExclamationIcon, 
    MagnifyingGlassIcon,
    ServerStackIcon,
    ArrowLeftIcon,
    HomeIcon,
    ChatBubbleLeftRightIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    status: Number,
});

const title = computed(() => {
    return {
        503: 'Serviço Indisponível',
        500: 'Erro Interno do Servidor',
        404: 'Página não Encontrada',
        403: 'Acesso Restrito',
    }[props.status] || 'Ocorreu um Erro';
});

const description = computed(() => {
    return {
        503: 'Desculpe, estamos realizando uma manutenção rápida. Voltaremos em breve!',
        500: 'Ops! Algo deu errado em nossos servidores. Nossa equipe já foi notificada.',
        404: 'A página que você está procurando não existe ou foi movida.',
        403: 'Você não tem permissão para acessar esta área. Por favor, contate o administrador se acreditar que isso é um erro.',
    }[props.status] || 'Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.';
});

const icon = computed(() => {
    if (props.status === 403) return ShieldExclamationIcon;
    if (props.status === 404) return MagnifyingGlassIcon;
    if (props.status === 500) return ServerStackIcon;
    return ExclamationTriangleIcon;
});

const iconColor = computed(() => {
    if (props.status === 403) return 'text-amber-500';
    if (props.status === 404) return 'text-primary-dynamic';
    if (props.status === 500) return 'text-rose-500';
    return 'text-slate-500';
});
const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = '/dashboard';
    }
};
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen bg-[var(--bg-main)] flex items-center justify-center p-6 transition-colors duration-300">
        <!-- Background Orbs -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-primary-dynamic/10 blur-[120px] rounded-full"></div>
            <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-rose-500/10 blur-[120px] rounded-full"></div>
        </div>

        <div class="relative w-full max-w-lg text-center">
            <!-- Icon & Status Code -->
            <div class="mb-8 relative inline-block">
                <div class="w-32 h-32 rounded-[2.5rem] bg-[var(--bg-card)] border border-[var(--border-color)] shadow-2xl flex items-center justify-center relative z-10 mx-auto">
                    <component :is="icon" class="w-16 h-16" :class="iconColor" />
                </div>
                <div class="absolute -top-4 -right-4 bg-[var(--bg-card)] border border-[var(--border-color)] px-4 py-1 rounded-full shadow-lg z-20">
                    <span class="text-xl font-black text-[var(--text-heading)] tracking-tighter">{{ status }}</span>
                </div>
            </div>

            <!-- Text Content -->
            <h1 class="text-4xl font-black text-[var(--text-heading)] tracking-tighter mb-4">
                {{ title }}
            </h1>
            <p class="text-slate-500 dark:text-slate-400 font-medium leading-relaxed mb-12 max-w-sm mx-auto">
                {{ description }}
            </p>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <Link 
                    href="/dashboard" 
                    class="w-full sm:w-auto px-8 py-4 bg-primary-dynamic text-white font-black uppercase tracking-widest text-[10px] rounded-full shadow-xl shadow-primary-dynamic/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2"
                >
                    <HomeIcon class="w-4 h-4" />
                    Voltar ao Início
                </Link>
                
                <button 
                    @click="goBack"
                    class="w-full sm:w-auto px-8 py-4 bg-[var(--bg-card)] border border-[var(--border-color)] text-[var(--text-heading)] font-black uppercase tracking-widest text-[10px] rounded-full hover:bg-slate-50 dark:hover:bg-slate-800 transition-all flex items-center justify-center gap-2 shadow-sm"
                >
                    <ArrowLeftIcon class="w-4 h-4" />
                    Página Anterior
                </button>
            </div>

            <!-- Support Link -->
            <div class="mt-12 pt-12 border-t border-[var(--border-color)]">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Ainda precisa de ajuda?</p>
                <div class="flex items-center justify-center gap-6">
                    <a href="#" class="flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-primary-dynamic transition-colors group">
                        <ChatBubbleLeftRightIcon class="w-4 h-4 group-hover:animate-bounce" />
                        Falar com Suporte
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
:root {
    --bg-main: #f8fafc;
    --bg-card: #ffffff;
    --border-color: rgba(226, 232, 240, 0.8);
    --text-heading: #0f172a;
}
.dark {
    --bg-main: #0f1014;
    --bg-card: #1b1c22;
    --border-color: rgba(255, 255, 255, 0.05);
    --text-heading: #f8fafc;
}
</style>
