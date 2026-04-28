<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const countdown = ref(5);

onMounted(() => {
    const timer = setInterval(() => {
        countdown.value--;
        if (countdown.value <= 0) {
            clearInterval(timer);
            router.get(route('adesao.create'));
        }
    }, 1000);
});
</script>

<template>
    <Head title="Adesão Concluída" />

    <GuestLayout>
        <div class="max-w-xl mx-auto text-center py-20 px-8 relative overflow-hidden">
            <!-- Background Decoration -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-64 h-64 bg-emerald-500/5 rounded-full blur-3xl -z-10"></div>

            <div class="flex items-center justify-center w-24 h-24 mx-auto mb-10 bg-emerald-500/10 text-emerald-500 rounded-3xl shadow-xl shadow-emerald-500/10 rotate-3 animate-in fade-in slide-in-from-bottom-8 duration-700">
                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            
            <h2 class="text-4xl lg:text-5xl font-black text-[var(--text-heading)] mb-6 tracking-tighter animate-in fade-in slide-in-from-top-4 duration-700 delay-150">Cadastro Enviado!</h2>
            
            <div class="space-y-6 mb-12 animate-in fade-in duration-700 delay-300">
                <p class="text-lg font-bold text-[var(--text-heading)] leading-relaxed">
                    Sua solicitação de adesão foi recebida com sucesso.
                </p>
                <p class="text-base font-medium text-[var(--text-main)] opacity-70 leading-relaxed max-w-md mx-auto">
                    Nossa equipe revisará seus dados e entraremos em contato em breve para os próximos passos da sua parceria.
                </p>
            </div>

            <div class="pt-8 border-t border-[var(--border-color)]/30 animate-in fade-in duration-700 delay-500">
                <div class="flex flex-col items-center gap-6">
                    <div class="flex items-center gap-4">
                        <div class="flex gap-1">
                            <div v-for="i in 5" :key="i" 
                                class="w-2 h-2 rounded-full transition-all duration-300"
                                :class="i <= (5 - countdown) ? 'bg-emerald-500 scale-125' : 'bg-slate-200 dark:bg-slate-700'"
                            ></div>
                        </div>
                        <p class="text-[10px] uppercase font-black tracking-[0.2em] text-[var(--text-main)]">
                            Reiniciando formulário em <span class="text-emerald-500 font-black text-xs">{{ countdown }}s</span>
                        </p>
                    </div>
                    
                    <Link :href="route('adesao.create')" class="inline-flex items-center gap-3 px-8 py-4 bg-[var(--bg-main)] hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 border border-[var(--border-color)] rounded-full text-[11px] font-black text-[var(--text-heading)] uppercase tracking-widest transition-all shadow-sm hover:shadow-md active:scale-95">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Voltar Agora
                    </Link>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
