<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Squares2X2Icon,
    BanknotesIcon,
    CurrencyDollarIcon,
    DocumentTextIcon,
    UserGroupIcon,
    ClipboardDocumentCheckIcon,
    BellIcon,
    Bars3Icon,
    XMarkIcon,
    ChevronRightIcon,
    UserIcon,
    ArrowLeftOnRectangleIcon,
    CheckCircleIcon,
    XCircleIcon,
    CheckBadgeIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

const page = usePage();
const tenant = computed(() => page.props.tenant);
const associado = computed(() => page.props.associado);
const primaryColor = computed(() => tenant.value?.primary_color || '#2563eb');
const showMobileMenu = ref(false);

const navItems = [
    { name: 'Meu Painel', href: '/cooperado', icon: Squares2X2Icon, routeMatch: 'cooperado.dashboard' },
    { name: 'Financeiro', href: '/cooperado/financeiro', icon: BanknotesIcon, routeMatch: 'cooperado.financeiro' },
    { name: 'Cotas Capital', href: '/cooperado/cotas', icon: CurrencyDollarIcon, routeMatch: 'cooperado.cotas' },
    { name: 'Reembolsos', href: '/cooperado/reembolsos', icon: ClipboardDocumentCheckIcon, routeMatch: 'cooperado.reembolsos' },
    { name: 'Dependentes', href: '/cooperado/dependentes', icon: UserGroupIcon, routeMatch: 'cooperado.dependentes' },
    { name: 'Documentos', href: '/cooperado/documentos', icon: DocumentTextIcon, routeMatch: 'cooperado.documentos' },
];

const iconMap = {
    BellIcon,
    BanknotesIcon,
    DocumentTextIcon,
    CheckCircleIcon,
    XCircleIcon,
    CheckBadgeIcon,
    ExclamationTriangleIcon
};

import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';

let pollingInterval = null;

onMounted(() => {
    pollingInterval = setInterval(() => {
        router.reload({ only: ['auth'] });
    }, 15000); // 15 seconds
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

function isActive(routeMatch) {
    try {
        return route().current(routeMatch);
    } catch {
        return false;
    }
}
</script>

<template>
    <div class="min-h-screen bg-[var(--bg-main)] transition-colors duration-300">
        <!-- Mobile Header -->
        <div class="lg:hidden sticky top-0 z-50 bg-[var(--bg-card)] border-b border-[var(--border-color)] shadow-sm">
            <div class="flex items-center justify-between px-4 h-16">
                <Link href="/cooperado" class="flex items-center gap-3">
                    <div :style="{ backgroundColor: primaryColor }" class="text-white p-2 rounded-xl shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                            <path d="M4.5 3.75a3 3 0 0 0-3 3v.75h21v-.75a3 3 0 0 0-3-3h-15Z" />
                            <path fill-rule="evenodd" d="M22.5 9.75h-21v7.5a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3v-7.5Zm-18 3.75a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5h-6a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <span class="font-black text-sm text-[var(--text-heading)] tracking-tight">Portal Cooperado</span>
                </Link>
                <div class="flex items-center gap-2">
                    <ThemeToggle />
                    <button @click="showMobileMenu = !showMobileMenu" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-[var(--text-muted)]">
                        <Bars3Icon v-if="!showMobileMenu" class="w-6 h-6" />
                        <XMarkIcon v-else class="w-6 h-6" />
                    </button>
                </div>
            </div>

            <!-- Mobile Menu Overlay -->
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="showMobileMenu" class="bg-[var(--bg-card)] border-b border-[var(--border-color)] shadow-lg pb-4">
                    <nav class="px-4 space-y-1 pt-2">
                        <Link
                            v-for="item in navItems"
                            :key="item.name"
                            :href="item.href"
                            @click="showMobileMenu = false"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all"
                            :class="isActive(item.routeMatch)
                                ? 'bg-primary-light text-primary-dynamic'
                                : 'text-[var(--text-muted)] hover:bg-slate-50 dark:hover:bg-slate-800'"
                        >
                            <component :is="item.icon" class="w-5 h-5" />
                            {{ item.name }}
                        </Link>
                    </nav>
                    <div class="mx-4 mt-4 pt-4 border-t border-[var(--border-color)]">
                        <div class="flex items-center gap-3 px-4 py-2">
                            <div :style="{ backgroundColor: primaryColor }" class="w-10 h-10 rounded-full flex items-center justify-center text-white font-black text-sm shadow-inner">
                                {{ $page.props.auth.user.name.charAt(0) }}
                            </div>
                            <div>
                                <p class="text-sm font-black text-[var(--text-heading)]">{{ $page.props.auth.user.name }}</p>
                                <p class="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wide">Cooperado</p>
                            </div>
                        </div>
                        <Link href="/cooperado/perfil" class="block px-4 py-2 text-sm font-bold text-[var(--text-muted)] hover:text-primary-dynamic transition-colors mt-2">Meu Perfil</Link>
                        <Link href="/logout" method="post" as="button" class="block w-full text-left px-4 py-2 text-sm font-bold text-rose-500 hover:text-rose-600 transition-colors">Sair</Link>
                    </div>
                </div>
            </Transition>
        </div>

        <div class="flex">
            <!-- Desktop Sidebar -->
            <aside class="hidden lg:flex flex-col w-64 min-h-screen bg-[var(--bg-card)] border-r border-[var(--border-color)] sticky top-0 h-screen transition-all duration-300">
                <!-- Sidebar Header -->
                <div class="p-6 border-b border-[var(--border-color)]">
                    <Link href="/cooperado" class="flex items-center gap-3 group">
                        <div :style="{ backgroundColor: primaryColor }" class="text-white p-2 rounded-lg shadow-sm transition-all group-hover:scale-105">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                <path d="M4.5 3.75a3 3 0 0 0-3 3v.75h21v-.75a3 3 0 0 0-3-3h-15Z" />
                                <path fill-rule="evenodd" d="M22.5 9.75h-21v7.5a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3v-7.5Zm-18 3.75a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5h-6a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-black text-[13px] text-[var(--text-heading)] tracking-tight leading-tight">Painel Cooperado</span>
                            <span class="text-[9px] font-bold text-[var(--text-muted)] uppercase tracking-widest mt-0.5">{{ tenant ? tenant.name : 'Siscoop3F' }}</span>
                        </div>
                    </Link>
                </div>

                <!-- Nav Links -->
                <nav class="flex-1 p-4 space-y-1 overflow-y-auto scrollbar-none">
                    <p class="px-4 mb-3 text-[9px] font-black text-[var(--text-muted)] opacity-50 uppercase tracking-[0.2em]">Principal</p>
                    <Link
                        v-for="item in navItems"
                        :key="item.name"
                        :href="item.href"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-xs font-bold transition-all group relative overflow-hidden"
                        :class="isActive(item.routeMatch)
                            ? 'bg-primary-light text-primary-dynamic'
                            : 'text-[var(--text-muted)] hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-[var(--text-heading)]'"
                    >
                        <component
                            :is="item.icon"
                            class="w-5 h-5 transition-transform group-hover:scale-110"
                            :class="isActive(item.routeMatch) ? 'text-primary-dynamic' : 'opacity-70 group-hover:opacity-100'"
                        />
                        <span class="relative z-10">{{ item.name }}</span>
                    </Link>
                </nav>

                <!-- Sidebar Footer -->
                <div class="px-6 py-6 border-t border-[var(--border-color)] bg-slate-50/50 dark:bg-slate-900/20">
                    <p class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-widest opacity-40">Siscoop3F v1.0</p>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 min-h-screen flex flex-col overflow-hidden">
                <!-- Top Header -->
                <header class="sticky top-0 z-40 bg-[var(--bg-main)]/80 backdrop-blur-md border-b border-[var(--border-color)] transition-all duration-300">
                    <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
                        <div>
                             <!-- Welcome dynamic content could go here -->
                        </div>

                        <div class="flex items-center gap-3">
                            <ThemeToggle />
                            
                            <div class="w-px h-5 bg-[var(--border-color)] mx-1"></div>

                            <!-- Notifications Dropdown -->
                            <Dropdown align="right" width="80">
                                <template #trigger>
                                    <button class="p-2 w-10 h-10 flex items-center justify-center rounded-lg border border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-muted)] hover:text-primary-dynamic hover:border-primary-dynamic/30 transition-all relative group">
                                        <BellIcon class="w-5 h-5 group-hover:rotate-12 transition-all" />
                                        <span v-if="$page.props.auth.unreadNotifications?.length > 0" class="absolute top-2 right-2.5 w-2.5 h-2.5 bg-rose-500 rounded-full border-2 border-[var(--bg-card)] animate-pulse"></span>
                                    </button>
                                </template>
                                <template #content>
                                    <div class="px-5 py-4 border-b border-[var(--border-color)] bg-slate-50 dark:bg-slate-800 flex justify-between items-center">
                                        <p class="text-[10px] font-black text-[var(--text-heading)] uppercase tracking-widest leading-none">Notificações</p>
                                        <button 
                                            v-if="$page.props.auth.unreadNotifications?.length > 0"
                                            @click="markAllAsRead" 
                                            class="text-[10px] font-bold text-primary-dynamic hover:underline"
                                        >
                                            Limpar Todas
                                        </button>
                                    </div>
                                    <div class="max-h-[350px] overflow-y-auto">
                                        <template v-if="$page.props.auth.unreadNotifications?.length > 0">
                                            <div 
                                                v-for="n in $page.props.auth.unreadNotifications" 
                                                :key="n.id" 
                                                @click="markAsRead(n.id, n.data.action_url)"
                                                class="px-5 py-4 border-b border-slate-50 dark:border-slate-800/50 hover:bg-slate-50 transition-all cursor-pointer flex gap-3"
                                            >
                                                <div class="w-8 h-8 rounded-full bg-primary-dynamic/10 text-primary-dynamic flex items-center justify-center shrink-0 mt-0.5">
                                                    <component :is="iconMap[n.data.icon] || iconMap.BellIcon" class="w-4 h-4" />
                                                </div>
                                                <div>
                                                    <p class="text-[12px] font-black text-[var(--text-heading)] mb-0.5">{{ n.data?.title }}</p>
                                                    <p class="text-[11px] font-medium text-[var(--text-muted)] leading-tight opacity-80">{{ n.data?.message }}</p>
                                                </div>
                                            </div>
                                        </template>
                                        <div v-else class="px-5 py-10 text-center flex flex-col items-center">
                                            <BellIcon class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600 mb-3" />
                                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Nenhuma notificação nova</span>
                                        </div>
                                    </div>
                                </template>
                            </Dropdown>

                            <!-- Profile Dropdown -->
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button class="flex items-center gap-2 p-1 px-2 rounded-lg border border-transparent hover:border-[var(--border-color)] hover:bg-white dark:hover:bg-slate-800 transition-all group h-10">
                                        <div :style="{ backgroundColor: primaryColor }" class="w-8 h-8 rounded flex items-center justify-center text-white font-black text-xs shadow-sm shrink-0">
                                            {{ $page.props.auth.user.name.charAt(0) }}
                                        </div>
                                        <div class="hidden sm:flex flex-col text-left pr-2">
                                            <span class="text-[11px] font-black text-[var(--text-heading)] leading-none tracking-tight">{{ $page.props.auth.user.name.split(' ')[0] }}</span>
                                            <span v-if="associado" class="text-[8px] font-bold text-[var(--text-muted)] uppercase tracking-widest mt-1 opacity-60">Matrícula #{{ associado.matricula }}</span>
                                        </div>
                                    </button>
                                </template>
                                <template #content>
                                    <div class="px-4 py-3 border-b border-[var(--border-color)]">
                                        <p class="text-[11px] font-black text-[var(--text-heading)] truncate tracking-tight">{{ $page.props.auth.user.email }}</p>
                                    </div>
                                    <div class="p-1 space-y-0.5">
                                        <DropdownLink href="/cooperado/perfil" class="flex items-center gap-2 px-3 py-2 rounded text-[11px] !font-bold">
                                            <UserIcon class="w-5 h-5 opacity-70" />
                                            <span>Perfil</span>
                                        </DropdownLink>
                                        <DropdownLink href="/logout" method="post" as="button" class="!text-rose-600 flex items-center gap-2 px-3 py-2 rounded text-[11px] !font-black">
                                            <ArrowLeftOnRectangleIcon class="w-5 h-5" />
                                            <span>Sair do Sistema</span>
                                        </DropdownLink>
                                    </div>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </header>

                <div class="p-6 lg:p-10 flex-1 max-w-6xl mx-auto w-full">
                    <!-- Page Title Area -->
                    <div v-if="$slots.header" class="mb-10">
                        <slot name="header" />
                    </div>

                    <!-- Content -->
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style>
:root {
    --primary-color: v-bind(primaryColor);
}
</style>

<style scoped>
:deep(.btn-primary) {
    background-color: v-bind(primaryColor) !important;
    color: #fff;
    padding: 0.6rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 800;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    transition: all 0.2s;
    box-shadow: var(--shadow-sm);
    border: none;
}
:deep(.btn-primary):hover { 
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}
:deep(.btn-primary):active { transform: translateY(0); }

:deep(.bg-primary-light) {
    background-color: color-mix(in srgb, v-bind(primaryColor) 10%, white);
    color: v-bind(primaryColor);
}
.dark :deep(.bg-primary-light) {
    background-color: color-mix(in srgb, v-bind(primaryColor) 15%, #0f172a);
}
:deep(.text-primary-dynamic) { color: v-bind(primaryColor); }
:deep(.bg-primary-dynamic) { background-color: v-bind(primaryColor) !important; }
:deep(.border-primary-dynamic) { border-color: v-bind(primaryColor) !important; }

/* Effects */
.scrollbar-none::-webkit-scrollbar {
    display: none;
}
</style>
