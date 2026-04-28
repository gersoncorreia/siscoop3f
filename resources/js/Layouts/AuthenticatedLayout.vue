<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import NavGroup from '@/Components/NavGroup.vue';
import NavGroupLink from '@/Components/NavGroupLink.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    Squares2X2Icon, 
    UserGroupIcon, 
    BanknotesIcon, 
    HeartIcon, 
    Cog6ToothIcon,
    BuildingLibraryIcon,
    CurrencyDollarIcon,
    ClipboardDocumentListIcon,
    BuildingOffice2Icon,
    DocumentTextIcon,
    BellIcon,
    MagnifyingGlassIcon,
    ShieldCheckIcon,
    CheckCircleIcon,
    XCircleIcon,
    UserPlusIcon,
    CheckBadgeIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

const showingNavigationDropdown = ref(false);
const page = usePage();

const tenant = computed(() => page.props.tenant);
const primaryColor = computed(() => tenant.value?.primary_color || '#2563eb');

const iconMap = {
    BellIcon,
    BanknotesIcon,
    DocumentTextIcon,
    UserPlusIcon,
    CheckCircleIcon,
    XCircleIcon,
    CheckBadgeIcon,
    ExclamationTriangleIcon
};

const dashboardRoute = computed(() => page.props.isCentral ? '/master-admin/dashboard' : '/dashboard');
const dashboardRouteName = computed(() => page.props.isCentral ? 'master-admin.dashboard' : 'dashboard');

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

</script>

<template>
    <div class="min-h-screen bg-[var(--bg-main)] transition-colors duration-300">
        <!-- Main Header & Navigation -->
        <nav class="sticky top-0 z-50 bg-[var(--bg-card)] transition-colors duration-300 shadow-sm">
            <!-- Top Utility Bar (Full Width Border) -->
            <div class="border-b border-[var(--border-color)]">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between items-center">
                        <div class="flex items-center gap-6 lg:gap-8 flex-1">
                            <!-- Logo -->
                            <Link :href="dashboardRoute" class="flex items-center gap-3 group shrink-0">
                                <div v-if="!tenant?.logo_url" :style="{ backgroundColor: primaryColor }" class="text-white p-2 rounded-xl shadow-lg transition-transform group-hover:scale-110">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                        <path d="M4.5 3.75a3 3 0 0 0-3 3v.75h21v-.75a3 3 0 0 0-3-3h-15Z" />
                                        <path fill-rule="evenodd" d="M22.5 9.75h-21v7.5a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3v-7.5Zm-18 3.75a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5h-6a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <img v-else :src="tenant.logo_url" class="h-9 w-auto object-contain" :alt="tenant.name" />
                                
                                <div class="hidden xl:flex flex-col">
                                    <span class="font-black text-sm text-[var(--text-heading)] tracking-tighter leading-none transition-colors">
                                        {{ tenant ? tenant.name : 'Siscoop3F Central' }}
                                    </span>
                                    <span class="text-[8px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5 whitespace-nowrap">Gestão Cooperativa</span>
                                </div>
                            </Link>

                            <!-- Integrated Navigation (Desktop) -->
                            <div class="hidden md:flex items-center space-x-4 lg:space-x-6">
                                <NavLink :href="dashboardRoute" :active="route().current(dashboardRouteName)">
                                    <Squares2X2Icon class="w-4 h-4 mr-2" />
                                    <span class="hidden lg:inline">Dashboard</span>
                                </NavLink>

                                <template v-if="$page.props.isTenant">
                                    <NavGroup title="Associados" :active="route().current('associados.*')">
                                        <template #icon><UserGroupIcon class="w-4 h-4 mr-2" /></template>
                                        <NavGroupLink href="/associados" :active="route().current('associados.index')">Central de Associados</NavGroupLink>
                                        <NavGroupLink href="/associados/dependentes" :active="route().current('associados.dependentes.*')">Familiar / Dependentes</NavGroupLink>
                                        <NavGroupLink href="/associados/adesoes" :active="route().current('associados.adesoes.*')">Adesões Pendentes</NavGroupLink>
                                        <NavGroupLink href="/associados/convenios" :active="route().current('associados.convenios.*')">Convênios</NavGroupLink>
                                    </NavGroup>

                                    <NavGroup title="Financeiro" :active="route().current('financeiro.*')">
                                        <template #icon><BanknotesIcon class="w-4 h-4 mr-2" /></template>
                                        <NavGroupLink href="/financeiro" :active="route().current('financeiro.index')">Painel Financeiro</NavGroupLink>
                                        <NavGroupLink href="/financeiro/lancamentos" :active="route().current('financeiro.lancamentos.*')">Extrato de Lançamentos</NavGroupLink>
                                        <NavGroupLink href="/financeiro/conciliacao" :active="route().current('financeiro.conciliacao')">Conciliação Bancária</NavGroupLink>
                                        <NavGroupLink href="/financeiro/contas" :active="route().current('financeiro.contas.*')">Contas da Cooperativa</NavGroupLink>
                                        <NavGroupLink href="/financeiro/capital" :active="route().current('financeiro.capital.*')">Gestão de Cota Capital</NavGroupLink>
                                    </NavGroup>

                                    <NavGroup title="Saúde" :active="route().current('saude.*')">
                                        <template #icon><HeartIcon class="w-4 h-4 mr-2" /></template>
                                        <NavGroupLink href="/saude/planos" :active="route().current('saude.planos.*')">Planos de Saúde</NavGroupLink>
                                        <NavGroupLink href="/saude/prestadores" :active="route().current('saude.prestadores.*')">Rede de Prestadores</NavGroupLink>
                                        <NavGroupLink href="/saude/reembolsos" :active="route().current('saude.reembolsos.*')">Pedidos de Reembolso</NavGroupLink>
                                    </NavGroup>

                                    <NavGroup v-if="$page.props.auth.permissions.includes('configuracoes.users')" title="Ajustes" :active="route().current('configuracoes.*')">
                                        <template #icon><Cog6ToothIcon class="w-4 h-4 mr-2" /></template>
                                        <NavGroupLink href="/configuracoes" :active="route().current('configuracoes.index')">Customização Visual</NavGroupLink>
                                        <NavGroupLink :href="route('configuracoes.usuarios.index')" :active="route().current('configuracoes.usuarios.*')">Gestão de Equipe</NavGroupLink>
                                    </NavGroup>
                                </template>
                            </div>

                            <!-- Search Bar (Desktop) - Pushed to right -->
                            <div class="hidden 2xl:flex items-center relative group ml-auto">
                                <MagnifyingGlassIcon class="w-4 h-4 absolute left-3 text-slate-400 group-focus-within:text-primary-dynamic transition-colors" />
                                <input 
                                    type="text" 
                                    placeholder="Busca rápida..." 
                                    class="pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800/50 border-none rounded-xl text-xs font-medium w-48 focus:w-64 focus:ring-1 focus:ring-primary-dynamic transition-all placeholder:text-slate-400"
                                />
                            </div>
                        </div>

                        <div class="flex items-center gap-2 sm:gap-4 ml-4">
                            <!-- Theme Toggle -->
                            <ThemeToggle />

                            <!-- Notifications -->
                            <div class="relative">
                                <Dropdown align="right" width="80">
                                    <template #trigger>
                                        <button class="p-2 rounded-full border border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-muted)] hover:text-primary-dynamic transition-all relative">
                                            <BellIcon class="w-5 h-5" />
                                            <span v-if="$page.props.auth.unreadNotifications && $page.props.auth.unreadNotifications.length > 0" class="absolute top-2 right-2.5 w-2.5 h-2.5 bg-rose-500 rounded-full border-2 border-[var(--bg-card)] animate-pulse"></span>
                                        </button>
                                    </template>

                                    <template #content>
                                        <div class="px-4 py-3 border-b border-slate-50 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 flex justify-between items-center">
                                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Notificações</p>
                                            <button 
                                                v-if="$page.props.auth.unreadNotifications && $page.props.auth.unreadNotifications.length > 0"
                                                @click="markAllAsRead" 
                                                class="text-[10px] font-bold text-primary-dynamic hover:underline"
                                            >
                                                Limpar Todas
                                            </button>
                                        </div>
                                        <div class="max-h-80 overflow-y-auto">
                                            <template v-if="$page.props.auth.unreadNotifications && $page.props.auth.unreadNotifications.length > 0">
                                                <div 
                                                    v-for="notification in $page.props.auth.unreadNotifications" 
                                                    :key="notification.id"
                                                    @click="markAsRead(notification.id, notification.data.action_url)"
                                                    class="px-4 py-3 border-b border-[var(--border-color)]/30 hover:bg-[var(--bg-main)]/50 cursor-pointer transition-colors flex gap-3"
                                                >
                                                    <div class="w-8 h-8 rounded-full bg-primary-dynamic/10 text-primary-dynamic flex items-center justify-center shrink-0 mt-0.5">
                                                        <component :is="iconMap[notification.data.icon] || iconMap.BellIcon" class="w-4 h-4" />
                                                    </div>
                                                    <div>
                                                        <p class="text-xs font-black text-[var(--text-heading)] leading-tight">{{ notification.data.title }}</p>
                                                        <p class="text-[10px] text-[var(--text-muted)] mt-1 leading-snug line-clamp-2">{{ notification.data.message }}</p>
                                                    </div>
                                                </div>
                                            </template>
                                            <div v-else class="px-4 py-8 text-center">
                                                <BellIcon class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600 mb-2" />
                                                <p class="text-xs font-bold text-slate-400">Nenhuma notificação nova</p>
                                            </div>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- Profile Dropdown -->
                            <div class="relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button class="flex items-center gap-3 p-1 pr-3 rounded-full border border-[var(--border-color)] bg-[var(--bg-card)] hover:shadow-sm transition-all group">
                                            <div :style="{ backgroundColor: primaryColor }" class="w-8 h-8 rounded-full flex items-center justify-center text-white font-black text-xs shadow-inner shrink-0">
                                                {{ $page.props.auth.user.name.charAt(0) }}
                                            </div>
                                            <div class="hidden sm:flex flex-col text-left">
                                                <span class="text-[11px] font-black text-[var(--text-heading)] leading-none truncate max-w-[120px]">{{ $page.props.auth.user.name }}</span>
                                                <span class="text-[8px] font-bold text-[var(--text-muted)] uppercase tracking-tighter mt-1">{{ tenant?.ramo || 'Admin' }}</span>
                                            </div>
                                        </button>
                                    </template>

                                    <template #content>
                                        <div class="px-4 py-3 border-b border-slate-50 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Minha Conta</p>
                                            <p class="text-xs font-bold text-slate-600 dark:text-slate-300 truncate">{{ $page.props.auth.user.email }}</p>
                                        </div>
                                        <DropdownLink :href="$page.props.isCentral ? '/master-admin/profile' : '/profile'" class="text-xs font-bold">Perfil</DropdownLink>
                                        <DropdownLink :href="$page.props.isCentral ? '/master-admin/logout' : '/logout'" method="post" as="button" class="text-xs font-bold text-rose-500">Sair</DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- Mobile Menu Hub -->
                            <button 
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="md:hidden p-2 rounded-full bg-primary-light"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation Menu -->
            <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="md:hidden border-t border-[var(--border-color)] bg-[var(--bg-card)]">
                <div class="space-y-1 pb-3 pt-2">
                    <ResponsiveNavLink :href="dashboardRoute" :active="route().current(dashboardRouteName)">Dashboard</ResponsiveNavLink>
                    <ResponsiveNavLink href="/associados" :active="route().current('associados.*')">Associados</ResponsiveNavLink>
                    <ResponsiveNavLink href="/financeiro" :active="route().current('financeiro.*')">Financeiro</ResponsiveNavLink>
                    <ResponsiveNavLink href="/saude/planos" :active="route().current('saude.*')">Saúde</ResponsiveNavLink>
                    <ResponsiveNavLink href="/configuracoes">Configurações</ResponsiveNavLink>
                </div>
            </div>
        </nav>

        <!-- Page Layout -->
        <main class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Page Title Area -->
                <div v-if="$slots.header" class="mb-8">
                    <slot name="header" />
                </div>
                
                <!-- Main Content -->
                <slot />
            </div>
        </main>
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
    @apply shadow-lg py-2.5 px-6 rounded-xl font-bold transition-all hover:scale-[1.02] active:scale-95;
}
:deep(.bg-primary-light) {
    background-color: color-mix(in srgb, v-bind(primaryColor) 10%, white);
    color: v-bind(primaryColor);
}
.dark :deep(.bg-primary-light) {
    background-color: color-mix(in srgb, v-bind(primaryColor) 15%, #1b1c22);
}
:deep(.text-primary-dynamic) { color: v-bind(primaryColor); }
:deep(.bg-primary-dynamic) { background-color: v-bind(primaryColor) !important; }
:deep(.border-primary-dynamic) { border-color: v-bind(primaryColor) !important; }
</style>
