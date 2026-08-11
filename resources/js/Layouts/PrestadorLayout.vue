<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    Squares2X2Icon,
    BuildingStorefrontIcon,
    DocumentTextIcon,
    BellIcon,
    Bars3Icon,
    XMarkIcon,
    UserIcon,
    UserCircleIcon,
    ArrowLeftOnRectangleIcon
} from '@heroicons/vue/24/outline';

const page = usePage();
const tenant = computed(() => page.props.tenant);
const primaryColor = computed(() => tenant.value?.primary_color || '#2563eb');
const showMobileMenu = ref(false);

const navItems = [
    { name: 'Painel de Atendimentos', href: '/prestador', icon: Squares2X2Icon, routeMatch: 'prestador.dashboard' },
    { name: 'Catálogo / Serviços', href: '/prestador/servicos', icon: BuildingStorefrontIcon, routeMatch: 'prestador.servicos' },
    { name: 'Faturamento Mensal', href: '/prestador/faturas', icon: DocumentTextIcon, routeMatch: 'prestador.faturas' },
    { name: 'Meu Perfil', href: '/prestador/perfil', icon: UserCircleIcon, routeMatch: 'prestador.perfil' },
];

let pollingInterval = null;

onMounted(() => {
    pollingInterval = setInterval(() => {
        router.reload({ only: ['auth'] });
    }, 15000);
});

onUnmounted(() => {
    if (pollingInterval) clearInterval(pollingInterval);
});

const markAsRead = (id, url) => {
    router.post(route('notifications.read', id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            if (url) {
                router.get(url);
            }
        }
    });
};

const markAllAsRead = () => {
    router.post(route('notifications.read-all'), {}, {
        preserveScroll: true
    });
};
</script>

<template>
    <div class="min-h-screen bg-[var(--bg-app)] text-[var(--text-body)] font-sans antialiased flex flex-col md:flex-row">
        
        <!-- Sidebar Navigation -->
        <aside class="hidden md:flex flex-col w-72 bg-[var(--bg-card)] border-r border-[var(--border-color)] shrink-0 z-20">
            <div class="h-20 flex items-center px-6 border-b border-[var(--border-color)]">
                <Link href="/prestador" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 bg-primary-dynamic/10 rounded-xl flex items-center justify-center border border-primary-dynamic/20 group-hover:scale-105 transition-transform">
                        <ApplicationLogo v-if="tenant?.logo_url" :url="tenant.logo_url" class="w-6 h-6 object-contain" />
                        <span v-else class="text-primary-dynamic font-black text-lg">{{ tenant?.name?.charAt(0) || 'S' }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-base font-black text-[var(--text-heading)] tracking-tight leading-none group-hover:text-primary-dynamic transition-colors">Portal do Parceiro</span>
                        <span class="text-[10px] text-[var(--text-muted)] font-bold uppercase tracking-widest mt-1">{{ tenant?.name || 'Siscoop3F' }}</span>
                    </div>
                </Link>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-1">
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 mt-4">Menu Principal</p>
                <Link v-for="item in navItems" :key="item.name" :href="item.href"
                    :class="[route().current(item.routeMatch) ? 'bg-primary-dynamic/10 text-primary-dynamic font-black' : 'text-[var(--text-muted)] hover:bg-slate-100 dark:hover:bg-slate-800/50 hover:text-[var(--text-heading)] font-bold', 'group flex items-center px-3 py-3 text-sm rounded-xl transition-all']">
                    <component :is="item.icon" :class="[route().current(item.routeMatch) ? 'text-primary-dynamic' : 'text-slate-400 group-hover:text-[var(--text-heading)]', 'mr-3 shrink-0 h-5 w-5 transition-colors']" aria-hidden="true" />
                    {{ item.name }}
                </Link>
            </div>
            
            <!-- Perfil / Logout na base -->
            <div class="p-4 border-t border-[var(--border-color)]">
                <div class="flex items-center justify-between mb-2 px-2">
                    <div class="flex items-center gap-2 overflow-hidden">
                        <div class="w-8 h-8 rounded-full bg-primary-dynamic/10 text-primary-dynamic flex items-center justify-center font-black text-xs shrink-0">
                            {{ $page.props.auth.user.name.charAt(0) }}
                        </div>
                        <div class="truncate">
                            <p class="text-xs font-black text-[var(--text-heading)] truncate">{{ $page.props.auth.user.name }}</p>
                            <p class="text-[10px] text-[var(--text-muted)] truncate">{{ $page.props.auth.user.email }}</p>
                        </div>
                    </div>
                </div>
                <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center justify-center px-3 py-2 text-xs font-bold text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-colors">
                    <ArrowLeftOnRectangleIcon class="mr-2 h-4 w-4" /> Sair da Conta
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Mobile Header -->
            <header class="md:hidden flex items-center justify-between h-16 px-4 bg-[var(--bg-card)] border-b border-[var(--border-color)] shrink-0 z-30">
                <Link href="/prestador" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-primary-dynamic/10 rounded-lg flex items-center justify-center border border-primary-dynamic/20">
                        <span class="text-primary-dynamic font-black text-sm">{{ tenant?.name?.charAt(0) || 'S' }}</span>
                    </div>
                    <span class="font-black text-[var(--text-heading)] tracking-tight">Portal do Parceiro</span>
                </Link>
                <div class="flex items-center gap-2">
                    <ThemeToggle />
                    <button @click="showMobileMenu = !showMobileMenu" class="p-2 rounded-lg text-[var(--text-muted)] hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <Bars3Icon v-if="!showMobileMenu" class="w-6 h-6" />
                        <XMarkIcon v-else class="w-6 h-6" />
                    </button>
                </div>
            </header>

            <!-- Mobile Menu Overlay -->
            <div v-if="showMobileMenu" class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm md:hidden" @click="showMobileMenu = false"></div>
            <div :class="['fixed inset-y-0 left-0 z-50 w-72 bg-[var(--bg-card)] border-r border-[var(--border-color)] transform transition-transform duration-300 ease-in-out md:hidden flex flex-col', showMobileMenu ? 'translate-x-0' : '-translate-x-full']">
                <div class="p-4 border-b border-[var(--border-color)] flex justify-end">
                    <button @click="showMobileMenu = false" class="p-2 rounded-lg text-[var(--text-muted)] hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-1">
                    <Link v-for="item in navItems" :key="item.name" :href="item.href" @click="showMobileMenu = false"
                        :class="[route().current(item.routeMatch) ? 'bg-primary-dynamic/10 text-primary-dynamic font-black' : 'text-[var(--text-muted)] hover:bg-slate-100 dark:hover:bg-slate-800/50 font-bold', 'group flex items-center px-3 py-3 text-sm rounded-xl transition-all']">
                        <component :is="item.icon" :class="[route().current(item.routeMatch) ? 'text-primary-dynamic' : 'text-slate-400', 'mr-3 shrink-0 h-5 w-5']" />
                        {{ item.name }}
                    </Link>
                </div>
                <div class="p-4 border-t border-[var(--border-color)]">
                     <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center px-3 py-2 text-sm font-bold text-rose-500 rounded-xl">
                        <ArrowLeftOnRectangleIcon class="mr-3 h-5 w-5" /> Sair
                    </Link>
                </div>
            </div>

            <!-- Page Header Desktop -->
            <header class="hidden md:flex items-center justify-between h-20 px-8 bg-[var(--bg-app)] shrink-0 z-10">
                <div class="flex items-center gap-4">
                    <slot name="header" />
                </div>
                <div class="flex items-center gap-4">
                    <ThemeToggle />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-[var(--bg-app)]">
                <!-- Mobile Header Slot -->
                <div class="md:hidden px-4 pt-6 pb-2">
                    <slot name="header" />
                </div>
                <div class="p-4 sm:p-6 lg:p-8 pt-4 sm:pt-6 w-full min-w-0">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.text-primary-dynamic { color: v-bind(primaryColor); }
.bg-primary-dynamic { background-color: v-bind(primaryColor) !important; }
.border-primary-dynamic { border-color: v-bind(primaryColor) !important; }
</style>
