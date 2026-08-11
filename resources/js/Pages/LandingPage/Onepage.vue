<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { 
    ShieldCheckIcon, 
    UserGroupIcon, 
    ArrowTrendingUpIcon,
    PhoneIcon,
    EnvelopeIcon,
    MapPinIcon,
    Bars3Icon,
    XMarkIcon
} from '@heroicons/vue/24/outline';
import { ref, computed } from 'vue';

const props = defineProps({
    settings: {
        type: Object,
        required: true
    },
    canLogin: {
        type: Boolean,
        default: true
    }
});

const mobileMenuOpen = ref(false);

const icons = {
    ShieldCheckIcon,
    UserGroupIcon,
    ArrowTrendingUpIcon,
    TrendingUpIcon: ArrowTrendingUpIcon // Fallback para compatibilidade
};

// Determinar o contraste da cor do texto sobre o fundo primário
const isDarkColor = (hex) => {
    if (!hex) return true;
    const c = hex.substring(1);
    const rgb = parseInt(c, 16);
    const r = (rgb >> 16) & 0xff;
    const g = (rgb >> 8) & 0xff;
    const b = (rgb >> 0) & 0xff;
    const luma = 0.2126 * r + 0.7152 * g + 0.0722 * b;
    return luma < 140; // True se for cor escura (precisa de texto branco)
};

const textOnPrimary = computed(() => {
    return isDarkColor(props.settings.primary_color) ? 'text-white' : 'text-slate-900';
});
</script>

<template>
    <Head :title="settings.hero_title || 'Início'" />

    <!-- Container geral com injeção de CSS customizado baseado no Tenant -->
    <div 
        :style="{
            '--primary': settings.primary_color,
            '--secondary': settings.secondary_color,
        }"
        class="min-h-screen bg-slate-50 text-slate-900 font-sans selection:bg-[var(--primary)] selection:text-white"
    >
        <!-- Top Bar / Header -->
        <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-100 transition-all duration-300">
            <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <img 
                        v-if="settings.logo_url" 
                        :src="settings.logo_url" 
                        alt="Logo" 
                        class="h-10 w-auto object-contain transition-transform duration-300 hover:scale-105"
                    />
                    <span class="text-xl font-black tracking-tight text-slate-900 flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-[var(--primary)] animate-pulse"></span>
                        {{ $page.props.tenant?.name || 'Siscoop3F' }}
                    </span>
                </div>

                <!-- Desktop Nav -->
                <nav class="hidden md:flex items-center gap-8 font-semibold text-sm text-slate-600">
                    <a v-if="settings.section_about_enabled" href="#sobre" class="hover:text-[var(--primary)] transition-all duration-200 hover:-translate-y-0.5">Sobre</a>
                    <a v-if="settings.section_features_enabled" href="#diferenciais" class="hover:text-[var(--primary)] transition-all duration-200 hover:-translate-y-0.5">Benefícios</a>
                    <a v-if="settings.section_contact_enabled" href="#contato" class="hover:text-[var(--primary)] transition-all duration-200 hover:-translate-y-0.5">Contato</a>
                </nav>

                <!-- Actions -->
                <div class="hidden md:flex items-center gap-4">
                    <Link 
                        v-if="canLogin" 
                        :href="route('login')" 
                        class="px-6 py-2.5 rounded-full border border-slate-200 hover:border-slate-300 font-bold text-sm text-slate-700 transition-all active:scale-95 hover:bg-slate-50"
                    >
                        Entrar no Portal
                    </Link>
                    <Link 
                        :href="route('adesao.create')" 
                        class="px-6 py-2.5 rounded-full bg-[var(--primary)] hover:opacity-90 font-bold text-sm transition-all shadow-md shadow-[var(--primary)]/10 active:scale-95 hover:-translate-y-0.5"
                        :class="textOnPrimary"
                    >
                        Seja um Cooperado
                    </Link>
                </div>

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-slate-600 transition-transform active:scale-90">
                    <Bars3Icon v-if="!mobileMenuOpen" class="w-6 h-6" />
                    <XMarkIcon v-else class="w-6 h-6" />
                </button>
            </div>

            <!-- Mobile Menu -->
            <div v-if="mobileMenuOpen" class="md:hidden border-t border-slate-100 bg-white px-6 py-8 space-y-6 shadow-xl animate-in slide-in-from-top duration-200">
                <nav class="flex flex-col gap-4 font-semibold text-slate-600">
                    <a v-if="settings.section_about_enabled" @click="mobileMenuOpen = false" href="#sobre" class="hover:text-[var(--primary)] transition-colors">Sobre</a>
                    <a v-if="settings.section_features_enabled" @click="mobileMenuOpen = false" href="#diferenciais" class="hover:text-[var(--primary)] transition-colors">Benefícios</a>
                    <a v-if="settings.section_contact_enabled" @click="mobileMenuOpen = false" href="#contato" class="hover:text-[var(--primary)] transition-colors">Contato</a>
                </nav>
                <div class="flex flex-col gap-3 pt-4 border-t border-slate-100">
                    <Link 
                        v-if="canLogin" 
                        :href="route('login')" 
                        class="w-full text-center py-3 rounded-xl border border-slate-200 font-bold text-sm text-slate-700"
                    >
                        Entrar no Portal
                    </Link>
                    <Link 
                        :href="route('adesao.create')" 
                        class="w-full text-center py-3 rounded-xl bg-[var(--primary)] font-bold text-sm shadow-sm"
                        :class="textOnPrimary"
                    >
                        Seja um Cooperado
                    </Link>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section v-if="settings.section_hero_enabled" class="relative overflow-hidden bg-gradient-to-br from-white via-white to-slate-100/50 py-16 lg:py-28">
            <!-- Background Blob Decors -->
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-[var(--primary)]/5 rounded-full blur-3xl -z-10"></div>
            <div class="absolute bottom-10 left-10 w-72 h-72 bg-[var(--secondary)]/5 rounded-full blur-3xl -z-10"></div>

            <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <!-- Text Content -->
                <div class="space-y-8 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[var(--primary)]/10 text-[var(--primary)] font-black text-xs uppercase tracking-widest animate-fade-up">
                        <span class="w-1.5 h-1.5 rounded-full bg-[var(--primary)] animate-ping"></span>
                        Espaço do Cooperado
                    </div>
                    
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 leading-tight tracking-tight animate-fade-up delay-100">
                        {{ settings.hero_title }}
                    </h1>
                    
                    <p class="text-lg text-slate-600 leading-relaxed font-medium animate-fade-up delay-200">
                        {{ settings.hero_subtitle }}
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 pt-2 animate-fade-up delay-300">
                        <Link 
                            :href="route('adesao.create')" 
                            class="px-8 py-4 bg-[var(--primary)] hover:opacity-95 font-bold rounded-2xl shadow-xl shadow-[var(--primary)]/15 hover:shadow-2xl hover:-translate-y-0.5 active:translate-y-0 transition-all text-center flex items-center justify-center gap-3"
                            :class="textOnPrimary"
                        >
                            Quero me Associar
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </Link>
                        <a 
                            v-if="settings.section_about_enabled" 
                            href="#sobre" 
                            class="px-8 py-4 bg-white border border-slate-200 hover:border-slate-300 font-bold rounded-2xl text-slate-700 hover:shadow-md transition-all text-center"
                        >
                            Conhecer a Cooperativa
                        </a>
                    </div>
                </div>

                <!-- Visual Content -->
                <div class="flex items-center justify-center lg:justify-end animate-fade-right">
                    <div class="relative w-full max-w-lg aspect-square lg:aspect-[4/3] rounded-3xl overflow-hidden bg-slate-100 border border-slate-200/50 shadow-2xl animate-float group">
                        <img 
                            v-if="settings.hero_image_url" 
                            :src="settings.hero_image_url" 
                            alt="Visual" 
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex flex-col items-center justify-center gap-4 bg-gradient-to-br from-[var(--primary)]/10 to-[var(--secondary)]/10 p-8 text-center">
                            <span class="text-xl font-black text-slate-800">{{ $page.props.tenant?.name }}</span>
                            <p class="text-sm font-semibold text-slate-500 max-w-xs">Gerencie esta imagem a qualquer momento diretamente no seu painel administrativo.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section v-if="settings.section_about_enabled" id="sobre" class="py-20 lg:py-32 bg-white relative">
            <div class="max-w-7xl mx-auto px-6">
                <div class="max-w-3xl mx-auto text-center space-y-6">
                    <h2 class="text-xs uppercase font-black tracking-[0.2em] text-[var(--primary)] animate-fade-up">Nossa Missão</h2>
                    <h3 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight animate-fade-up delay-100">{{ settings.about_title }}</h3>
                    <p class="text-lg text-slate-600 leading-relaxed font-medium pt-2 animate-fade-up delay-200">
                        {{ settings.about_text }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Features / Benefícios Section -->
        <section v-if="settings.section_features_enabled" id="diferenciais" class="py-20 lg:py-32 bg-slate-50 border-y border-slate-100 relative">
            <div class="max-w-7xl mx-auto px-6 space-y-16">
                <div class="text-center space-y-4">
                    <h2 class="text-xs uppercase font-black tracking-[0.2em] text-[var(--primary)]">Por Que Nós?</h2>
                    <h3 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">O Cooperativismo Gera Resultados</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div 
                        v-for="(feature, index) in settings.features" 
                        :key="index" 
                        class="p-8 bg-white border border-slate-200/50 rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 group hover:-translate-y-2 flex flex-col justify-between"
                    >
                        <div class="space-y-6">
                            <div class="inline-flex p-4 rounded-2xl bg-[var(--primary)]/10 text-[var(--primary)] transition-all duration-300 group-hover:scale-110">
                                <component :is="icons[feature.icon] || ShieldCheckIcon" class="w-6 h-6" />
                            </div>
                            <h4 class="text-xl font-bold text-slate-900 group-hover:text-[var(--primary)] transition-colors">{{ feature.title }}</h4>
                            <p class="text-slate-600 font-medium leading-relaxed">{{ feature.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section v-if="settings.section_contact_enabled" id="contato" class="py-20 lg:py-32 bg-white relative">
            <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-16 items-start">
                <div class="space-y-8">
                    <div class="space-y-4">
                        <h2 class="text-xs uppercase font-black tracking-[0.2em] text-[var(--primary)]">Fale Conosco</h2>
                        <h3 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Estamos Prontos para Atender</h3>
                        <p class="text-slate-600 font-medium leading-relaxed">
                            Ficou com alguma dúvida ou gostaria de conhecer mais sobre os benefícios de cooperar conosco? Entre em contato ou visite nossa sede.
                        </p>
                    </div>

                    <div class="space-y-6 font-semibold">
                        <div v-if="settings.contact_phone" class="flex items-center gap-4 text-slate-700 transition-transform hover:translate-x-1 duration-200">
                            <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl">
                                <PhoneIcon class="w-5 h-5 text-[var(--primary)]" />
                            </div>
                            <span>{{ settings.contact_phone }}</span>
                        </div>
                        <div v-if="settings.contact_email" class="flex items-center gap-4 text-slate-700 transition-transform hover:translate-x-1 duration-200">
                            <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl">
                                <EnvelopeIcon class="w-5 h-5 text-[var(--primary)]" />
                            </div>
                            <span>{{ settings.contact_email }}</span>
                        </div>
                        <div v-if="settings.contact_address" class="flex items-center gap-4 text-slate-700 transition-transform hover:translate-x-1 duration-200">
                            <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl">
                                <MapPinIcon class="w-5 h-5 text-[var(--primary)]" />
                            </div>
                            <span>{{ settings.contact_address }}</span>
                        </div>
                    </div>

                    <!-- Redes Sociais -->
                    <div class="flex gap-4 pt-4">
                        <a v-if="settings.social_facebook" :href="settings.social_facebook" target="_blank" class="p-3 rounded-full border border-slate-200 hover:border-slate-300 text-slate-500 hover:text-[var(--primary)] hover:-translate-y-1 transition-all">
                            <span class="sr-only">Facebook</span>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c4.56-.93 8-4.96 8-9.75z"/></svg>
                        </a>
                        <a v-if="settings.social_instagram" :href="settings.social_instagram" target="_blank" class="p-3 rounded-full border border-slate-200 hover:border-slate-300 text-slate-500 hover:text-[var(--primary)] hover:-translate-y-1 transition-all">
                            <span class="sr-only">Instagram</span>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </a>
                        <a v-if="settings.social_linkedin" :href="settings.social_linkedin" target="_blank" class="p-3 rounded-full border border-slate-200 hover:border-slate-300 text-slate-500 hover:text-[var(--primary)] hover:-translate-y-1 transition-all">
                            <span class="sr-only">LinkedIn</span>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Formulário de Contato Rápido -->
                <div class="p-8 bg-slate-50 border border-slate-200/50 rounded-3xl w-full hover:shadow-lg transition-shadow duration-300">
                    <h4 class="text-xl font-bold text-slate-900 mb-6">Envie uma Mensagem</h4>
                    <form @submit.prevent class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Seu Nome</label>
                            <input type="text" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[var(--primary)] bg-white text-sm transition-all focus:ring-1 focus:ring-[var(--primary)]" placeholder="Ex: João Silva" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Seu E-mail</label>
                            <input type="email" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[var(--primary)] bg-white text-sm transition-all focus:ring-1 focus:ring-[var(--primary)]" placeholder="joao@email.com" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Mensagem</label>
                            <textarea rows="4" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[var(--primary)] bg-white text-sm resize-none" placeholder="Como podemos ajudar você?"></textarea>
                        </div>
                        <button type="submit" class="w-full py-3.5 bg-[var(--primary)] rounded-xl font-semibold text-sm shadow-md transition-opacity hover:opacity-95" :class="textOnPrimary">
                            Enviar Mensagem
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-3">
                    <span class="text-white font-extrabold text-lg">{{ $page.props.tenant?.name || 'Siscoop3F' }}</span>
                </div>
                
                <p class="text-sm font-medium">
                    &copy; {{ new Date().getFullYear() }} {{ $page.props.tenant?.name }}. Todos os direitos reservados.
                </p>

                <div class="flex gap-6 text-sm font-semibold">
                    <Link :href="route('login')" class="hover:text-white transition-colors">Portal Administrativo</Link>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(24px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(40px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0) rotate(0.5deg);
    }
    50% {
        transform: translateY(-12px) rotate(-0.5deg);
    }
}

.animate-fade-up {
    opacity: 0;
    animation: fadeInUp 0.9s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.animate-fade-right {
    opacity: 0;
    animation: fadeInRight 0.9s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

.delay-100 {
    animation-delay: 120ms;
}

.delay-200 {
    animation-delay: 240ms;
}

.delay-300 {
    animation-delay: 360ms;
}
</style>
