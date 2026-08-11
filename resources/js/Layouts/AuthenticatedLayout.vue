<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import swal from '@/Utils/swal';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
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
    ExclamationTriangleIcon,
    ChartPieIcon,
    Bars3Icon,
    XMarkIcon,
    ChevronDownIcon,
    ChevronRightIcon,
    ArrowLeftOnRectangleIcon
} from '@heroicons/vue/24/outline';

const page = usePage();
const tenant = computed(() => page.props.tenant);
const primaryColor = computed(() => tenant.value?.primary_color || '#2563eb');

const isCentral = computed(() => page.props.isCentral);
const isTenant = computed(() => page.props.isTenant);
const isFundacao = computed(() => tenant.value?.tipo === 'fundacao');

const dashboardRoute = computed(() => isCentral.value ? '/master-admin/dashboard' : '/dashboard');
const dashboardRouteName = computed(() => isCentral.value ? 'master-admin.dashboard' : 'dashboard');

// Helper: verifica se o tenant tem um determinado serviço habilitado
const hasServico = (key) => {
    const servicos = tenant.value?.servicos;
    if (!servicos && tenant.value?.tipo === 'cooperativa') return true;
    return Array.isArray(servicos) && servicos.includes(key);
};

// Sidebar States
const isSidebarCollapsed = ref(false); // Desktop compact mode
const isMobileSidebarOpen = ref(false); // Mobile drawer mode

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
};

const toggleMobileSidebar = () => {
    isMobileSidebarOpen.value = !isMobileSidebarOpen.value;
};

// Accordion Open States (Default open if active route)
const groupState = ref({
    associados: route().current('associados.*'),
    financeiro: route().current('financeiro.*'),
    saude: route().current('saude.*'),
    ajustes: route().current('configuracoes.*'),
});

const toggleGroup = (key) => {
    groupState.value[key] = !groupState.value[key];
};

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

// Monitorar flash messages do Laravel/Inertia
watch(() => page.props.flash?.success, (success) => {
    if (success) {
        swal.success(success);
        if (page.props.flash) {
            page.props.flash.success = null;
        }
    }
}, { immediate: true });

watch(() => page.props.flash?.error, (error) => {
    if (error) {
        swal.error(error);
        if (page.props.flash) {
            page.props.flash.error = null;
        }
    }
}, { immediate: true });
</script>

<template>
    <div class="min-h-screen bg-[var(--bg-main)] text-[var(--text-heading)] transition-colors duration-300 flex flex-col">
        
        <!-- TOP NAVBAR (Clean Utility Bar) -->
        <header class="sticky top-0 z-40 bg-[var(--bg-card)] border-b border-[var(--border-color)] h-16 flex items-center justify-between px-4 sm:px-6 shadow-sm transition-colors">
            
            <!-- Left Side: Toggle Button + Logo / Entity Title -->
            <div class="flex items-center gap-3 sm:gap-4">
                <!-- Mobile Drawer Toggle -->
                <button 
                    @click="toggleMobileSidebar" 
                    class="xl:hidden p-2 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                    aria-label="Abrir menu"
                >
                    <Bars3Icon class="w-6 h-6" />
                </button>

                <!-- Desktop Sidebar Collapse Toggle -->
                <button 
                    @click="toggleSidebar" 
                    class="hidden xl:flex p-2 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                    aria-label="Recolher menu"
                    :title="isSidebarCollapsed ? 'Expandir menu' : 'Recolher menu'"
                >
                    <Bars3Icon class="w-5 h-5" />
                </button>

                <!-- Logo & Entity Subtitle -->
                <Link :href="dashboardRoute" class="flex items-center gap-3 group shrink-0">
                    <div v-if="!tenant?.logo_url" :style="{ backgroundColor: primaryColor }" class="text-white p-2 rounded-xl shadow-md transition-transform group-hover:scale-105">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                            <path d="M4.5 3.75a3 3 0 0 0-3 3v.75h21v-.75a3 3 0 0 0-3-3h-15Z" />
                            <path fill-rule="evenodd" d="M22.5 9.75h-21v7.5a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3v-7.5Zm-18 3.75a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5h-6a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <img v-else :src="tenant.logo_url" class="h-9 w-auto object-contain" :alt="tenant.name" />
                    
                    <div class="flex flex-col">
                        <span class="font-black text-sm text-[var(--text-heading)] tracking-tight leading-none transition-colors">
                            {{ tenant ? tenant.name : 'Siscoop3F Central' }}
                        </span>
                        <span class="text-[8px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5 whitespace-nowrap">
                            {{ tenant?.tipo === 'fundacao' ? 'Gestão de Fundação' : 'Gestão Cooperativa' }}
                        </span>
                    </div>
                </Link>
            </div>

            <!-- Right Side Utility Tools: Search + Theme + Notifications + User Profile -->
            <div class="flex items-center gap-2 sm:gap-4">
                
                <!-- Quick Search (Desktop) -->
                <div class="hidden md:flex items-center relative group">
                    <MagnifyingGlassIcon class="w-4 h-4 absolute left-3 text-slate-400 group-focus-within:text-primary-dynamic transition-colors" />
                    <input 
                        type="text" 
                        placeholder="Busca rápida..." 
                        class="pl-9 pr-4 py-1.5 bg-slate-100 dark:bg-slate-800/60 border-none rounded-xl text-xs font-medium w-40 focus:w-56 focus:ring-1 focus:ring-primary-dynamic transition-all placeholder:text-slate-400"
                    />
                </div>

                <!-- Theme Toggle (Dark/Light) -->
                <ThemeToggle />

                <!-- Notifications Dropdown -->
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

                <!-- User Profile Dropdown -->
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

            </div>
        </header>

        <!-- BODY WRAPPER (SIDEBAR + MAIN CONTENT AREA) -->
        <div class="flex-1 flex relative">
            
            <!-- DESKTOP LEFT SIDEBAR -->
            <aside 
                class="hidden xl:flex flex-col border-r border-[var(--border-color)] bg-[var(--bg-card)] transition-all duration-300 shrink-0 sticky top-16 h-[calc(100vh-4rem)] overflow-y-auto select-none"
                :class="isSidebarCollapsed ? 'w-20 items-center py-6 px-2' : 'w-64 py-6 px-4'"
            >
                <nav class="space-y-2 w-full">
                    
                    <!-- 1. DASHBOARD LINK -->
                    <Link 
                        :href="dashboardRoute" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                        :class="route().current(dashboardRouteName) ? 'bg-primary-light text-primary-dynamic shadow-sm font-black' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-[var(--text-heading)]'"
                        :title="isSidebarCollapsed ? 'Dashboard' : ''"
                    >
                        <Squares2X2Icon class="w-5 h-5 shrink-0" />
                        <span v-if="!isSidebarCollapsed" class="truncate">Dashboard</span>
                    </Link>

                    <!-- TENANT NAVIGATION GROUPS -->
                    <template v-if="isTenant">
                        
                        <!-- 2. CLIENTES / ASSOCIADOS -->
                        <div class="space-y-1 pt-2 border-t border-[var(--border-color)]/50">
                            <button 
                                @click="toggleGroup('associados')" 
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                                :class="route().current('associados.*') ? 'text-primary-dynamic font-black' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                                :title="isSidebarCollapsed ? (isFundacao ? 'Clientes' : 'Associados') : ''"
                            >
                                <div class="flex items-center gap-3 truncate">
                                    <UserGroupIcon class="w-5 h-5 shrink-0" />
                                    <span v-if="!isSidebarCollapsed" class="truncate">{{ isFundacao ? 'Clientes' : 'Associados' }}</span>
                                </div>
                                <ChevronDownIcon v-if="!isSidebarCollapsed" class="w-4 h-4 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': groupState.associados }" />
                            </button>

                            <!-- Submenu Items -->
                            <div v-show="groupState.associados && !isSidebarCollapsed" class="pl-9 space-y-1 py-1">
                                <Link 
                                    href="/associados" 
                                    class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors"
                                    :class="route().current('associados.index') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
                                >
                                    Central de {{ isFundacao ? 'Clientes' : 'Associados' }}
                                </Link>
                                <Link 
                                    href="/associados/dependentes" 
                                    class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors"
                                    :class="route().current('associados.dependentes.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
                                >
                                    Familiar / Dependentes
                                </Link>
                                <Link 
                                    href="/associados/adesoes" 
                                    class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors"
                                    :class="route().current('associados.adesoes.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
                                >
                                    Adesões Pendentes
                                </Link>
                                <Link 
                                    href="/associados/convenios" 
                                    class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors"
                                    :class="route().current('associados.convenios.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
                                >
                                    Convênios
                                </Link>
                            </div>
                        </div>

                        <!-- 3. FINANCEIRO -->
                        <div class="space-y-1 pt-2 border-t border-[var(--border-color)]/50">
                            <button 
                                @click="toggleGroup('financeiro')" 
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                                :class="route().current('financeiro.*') ? 'text-primary-dynamic font-black' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                                :title="isSidebarCollapsed ? 'Financeiro' : ''"
                            >
                                <div class="flex items-center gap-3 truncate">
                                    <BanknotesIcon class="w-5 h-5 shrink-0" />
                                    <span v-if="!isSidebarCollapsed" class="truncate">Financeiro</span>
                                </div>
                                <ChevronDownIcon v-if="!isSidebarCollapsed" class="w-4 h-4 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': groupState.financeiro }" />
                            </button>

                            <!-- Submenu Items -->
                            <div v-show="groupState.financeiro && !isSidebarCollapsed" class="pl-9 space-y-1 py-1">
                                <Link 
                                    href="/financeiro" 
                                    class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors"
                                    :class="route().current('financeiro.index') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
                                >
                                    Painel Financeiro
                                </Link>
                                <Link 
                                    href="/financeiro/lancamentos" 
                                    class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors"
                                    :class="route().current('financeiro.lancamentos.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
                                >
                                    Extrato de Lançamentos
                                </Link>
                                <Link 
                                    href="/financeiro/conciliacao" 
                                    class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors"
                                    :class="route().current('financeiro.conciliacao') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
                                >
                                    Conciliação Bancária
                                </Link>
                                <Link 
                                    href="/financeiro/contas" 
                                    class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors"
                                    :class="route().current('financeiro.contas.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
                                >
                                    Contas da {{ isFundacao ? 'Fundação' : 'Cooperativa' }}
                                </Link>
                                <Link 
                                    v-if="!isFundacao"
                                    href="/financeiro/capital" 
                                    class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors"
                                    :class="route().current('financeiro.capital.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
                                >
                                    Gestão de Cota Capital
                                </Link>
                            </div>
                        </div>

                        <!-- 4. SAÚDE / CLUBE DE SAÚDE -->
                        <div class="space-y-1 pt-2 border-t border-[var(--border-color)]/50">
                            <button 
                                @click="toggleGroup('saude')" 
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                                :class="route().current('saude.*') ? 'text-primary-dynamic font-black' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                                :title="isSidebarCollapsed ? (isFundacao ? 'Clube de Saúde' : 'Saúde') : ''"
                            >
                                <div class="flex items-center gap-3 truncate">
                                    <HeartIcon class="w-5 h-5 shrink-0" />
                                    <span v-if="!isSidebarCollapsed" class="truncate">{{ isFundacao ? 'Clube de Saúde' : 'Saúde' }}</span>
                                </div>
                                <ChevronDownIcon v-if="!isSidebarCollapsed" class="w-4 h-4 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': groupState.saude }" />
                            </button>

                            <!-- Submenu Items -->
                            <div v-show="groupState.saude && !isSidebarCollapsed" class="pl-9 space-y-1 py-1">
                                <template v-if="!isFundacao">
                                    <Link href="/saude/planos" class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors" :class="route().current('saude.planos.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'">Planos de Saúde</Link>
                                    <Link href="/saude/prestadores" class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors" :class="route().current('saude.prestadores.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'">Rede de Prestadores</Link>
                                    <Link href="/saude/autorizacoes" class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors" :class="route().current('saude.autorizacoes.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'">Autorizações (Rede)</Link>
                                    <Link href="/saude/faturas" class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors" :class="route().current('saude.faturas.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'">Faturamento (Dia 05)</Link>
                                    <Link href="/saude/reembolsos" class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors" :class="route().current('saude.reembolsos.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'">Pedidos de Reembolso</Link>
                                </template>
                                <template v-else>
                                    <Link v-if="hasServico('gestao_consultas')" href="/saude/agendamentos" class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors" :class="route().current('saude.agendamentos.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'">Agendamentos</Link>
                                    <Link v-if="hasServico('tabela_servicos')" href="/saude/tabela-servicos" class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors" :class="route().current('saude.tabela-servicos.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'">Tabela de Serviços</Link>
                                    <Link v-if="hasServico('tabela_servicos')" href="/saude/planos-adesao" class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors" :class="route().current('saude.planos-adesao.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'">Planos de Adesão</Link>
                                    <Link v-if="hasServico('gestao_internacoes')" href="/saude/internacoes" class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors" :class="route().current('saude.internacoes.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'">Internações Eletivas</Link>
                                    <Link v-if="hasServico('campanhas_saude')" href="/saude/campanhas" class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors" :class="route().current('saude.campanhas.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'">Campanhas de Saúde</Link>
                                    <Link v-if="hasServico('indicadores_desempenho')" href="/saude/indicadores" class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors" :class="route().current('saude.indicadores.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'">Indicadores</Link>
                                    <Link href="/saude/prestadores" class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors" :class="route().current('saude.prestadores.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'">Rede Credenciada</Link>
                                    <Link href="/saude/autorizacoes" class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors" :class="route().current('saude.autorizacoes.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'">Autorizações</Link>
                                    <Link href="/saude/faturas" class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors" :class="route().current('saude.faturas.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'">Faturamento</Link>
                                    <Link v-if="hasServico('relatorios_gerenciais')" href="/saude/reembolsos" class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors" :class="route().current('saude.reembolsos.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'">Solicitações</Link>
                                </template>
                            </div>
                        </div>

                        <!-- 5. RELATÓRIOS -->
                        <div v-if="$page.props.auth.permissions.includes('relatorios.view')" class="space-y-1 pt-2 border-t border-[var(--border-color)]/50">
                            <Link 
                                :href="route('relatorios.index')" 
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                                :class="route().current('relatorios.*') ? 'bg-primary-light text-primary-dynamic shadow-sm font-black' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-[var(--text-heading)]'"
                                :title="isSidebarCollapsed ? 'Relatórios' : ''"
                            >
                                <ChartPieIcon class="w-5 h-5 shrink-0" />
                                <span v-if="!isSidebarCollapsed" class="truncate">Relatórios</span>
                            </Link>
                        </div>

                        <!-- 6. AJUSTES / CONFIGURAÇÕES -->
                        <div v-if="$page.props.auth.permissions.includes('configuracoes.users')" class="space-y-1 pt-2 border-t border-[var(--border-color)]/50">
                            <button 
                                @click="toggleGroup('ajustes')" 
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                                :class="route().current('configuracoes.*') ? 'text-primary-dynamic font-black' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                                :title="isSidebarCollapsed ? 'Ajustes' : ''"
                            >
                                <div class="flex items-center gap-3 truncate">
                                    <Cog6ToothIcon class="w-5 h-5 shrink-0" />
                                    <span v-if="!isSidebarCollapsed" class="truncate">Ajustes</span>
                                </div>
                                <ChevronDownIcon v-if="!isSidebarCollapsed" class="w-4 h-4 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': groupState.ajustes }" />
                            </button>

                            <!-- Submenu Items -->
                            <div v-show="groupState.ajustes && !isSidebarCollapsed" class="pl-9 space-y-1 py-1">
                                <Link href="/configuracoes" class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors" :class="route().current('configuracoes.index') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'">Customização Visual</Link>
                                <Link :href="route('configuracoes.pagamentos')" class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors" :class="route().current('configuracoes.pagamentos') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'">Ajustes de Pagamentos</Link>
                                <Link :href="route('configuracoes.contratos')" class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors" :class="route().current('configuracoes.contratos*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'">Contratos Operacionais</Link>
                                <Link :href="route('configuracoes.usuarios.index')" class="block py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors" :class="route().current('configuracoes.usuarios.*') ? 'text-primary-dynamic font-bold bg-primary-light/50' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'">Gestão de Equipe</Link>
                            </div>
                        </div>

                    </template>

                </nav>

                <!-- Sidebar Footer Status -->
                <div v-if="!isSidebarCollapsed" class="mt-auto pt-4 border-t border-[var(--border-color)] text-[10px] text-slate-400 font-medium">
                    <p class="truncate font-bold text-slate-500">{{ tenant ? tenant.nome : 'Painel Master' }}</p>
                    <p class="truncate text-[9px] uppercase tracking-wider text-slate-400 mt-0.5">Siscoop3F v2.4</p>
                </div>
            </aside>

            <!-- MOBILE DRAWER SIDEBAR (< xl screens) -->
            <Transition name="fade">
                <div 
                    v-if="isMobileSidebarOpen" 
                    @click="isMobileSidebarOpen = false" 
                    class="xl:hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
                ></div>
            </Transition>

            <Transition name="slide">
                <aside 
                    v-if="isMobileSidebarOpen"
                    class="xl:hidden fixed left-0 top-0 bottom-0 z-50 w-72 bg-[var(--bg-card)] border-r border-[var(--border-color)] flex flex-col p-6 overflow-y-auto shadow-2xl"
                >
                    <!-- Drawer Header -->
                    <div class="flex items-center justify-between pb-6 border-b border-[var(--border-color)] mb-4">
                        <div class="flex items-center gap-3">
                            <div :style="{ backgroundColor: primaryColor }" class="text-white p-2 rounded-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                    <path d="M4.5 3.75a3 3 0 0 0-3 3v.75h21v-.75a3 3 0 0 0-3-3h-15Z" />
                                    <path fill-rule="evenodd" d="M22.5 9.75h-21v7.5a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3v-7.5Zm-18 3.75a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5h-6a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-black text-sm text-[var(--text-heading)] leading-none">{{ tenant ? tenant.name : 'Siscoop3F Central' }}</h3>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mt-1">{{ tenant?.tipo === 'fundacao' ? 'Fundação' : 'Cooperativa' }}</p>
                            </div>
                        </div>
                        <button @click="isMobileSidebarOpen = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Drawer Nav Items -->
                    <nav class="space-y-3 flex-1">
                        <Link 
                            :href="dashboardRoute" 
                            @click="isMobileSidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                            :class="route().current(dashboardRouteName) ? 'bg-primary-light text-primary-dynamic font-black' : 'text-slate-600 dark:text-slate-400'"
                        >
                            <Squares2X2Icon class="w-5 h-5" />
                            <span>Dashboard</span>
                        </Link>

                        <template v-if="isTenant">
                            <!-- Clientes/Associados -->
                            <div class="space-y-1">
                                <button @click="toggleGroup('associados')" class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold text-slate-600 dark:text-slate-400">
                                    <div class="flex items-center gap-3">
                                        <UserGroupIcon class="w-5 h-5" />
                                        <span>{{ isFundacao ? 'Clientes' : 'Associados' }}</span>
                                    </div>
                                    <ChevronDownIcon class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': groupState.associados }" />
                                </button>
                                <div v-show="groupState.associados" class="pl-9 space-y-1">
                                    <Link href="/associados" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">Central de {{ isFundacao ? 'Clientes' : 'Associados' }}</Link>
                                    <Link href="/associados/dependentes" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">Familiar / Dependentes</Link>
                                    <Link href="/associados/adesoes" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">Adesões Pendentes</Link>
                                    <Link href="/associados/convenios" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">Convênios</Link>
                                </div>
                            </div>

                            <!-- Financeiro -->
                            <div class="space-y-1">
                                <button @click="toggleGroup('financeiro')" class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold text-slate-600 dark:text-slate-400">
                                    <div class="flex items-center gap-3">
                                        <BanknotesIcon class="w-5 h-5" />
                                        <span>Financeiro</span>
                                    </div>
                                    <ChevronDownIcon class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': groupState.financeiro }" />
                                </button>
                                <div v-show="groupState.financeiro" class="pl-9 space-y-1">
                                    <Link href="/financeiro" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">Painel Financeiro</Link>
                                    <Link href="/financeiro/lancamentos" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">Extrato de Lançamentos</Link>
                                    <Link href="/financeiro/conciliacao" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">Conciliação Bancária</Link>
                                    <Link href="/financeiro/contas" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">Contas Bancárias</Link>
                                    <Link v-if="!isFundacao" href="/financeiro/capital" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">Gestão de Cota Capital</Link>
                                </div>
                            </div>

                            <!-- Saúde / Clube de Saúde -->
                            <div class="space-y-1">
                                <button @click="toggleGroup('saude')" class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold text-slate-600 dark:text-slate-400">
                                    <div class="flex items-center gap-3">
                                        <HeartIcon class="w-5 h-5" />
                                        <span>{{ isFundacao ? 'Clube de Saúde' : 'Saúde' }}</span>
                                    </div>
                                    <ChevronDownIcon class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': groupState.saude }" />
                                </button>
                                <div v-show="groupState.saude" class="pl-9 space-y-1">
                                    <template v-if="!isFundacao">
                                        <Link href="/saude/planos" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500">Planos de Saúde</Link>
                                        <Link href="/saude/prestadores" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500">Rede de Prestadores</Link>
                                        <Link href="/saude/autorizacoes" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500">Autorizações (Rede)</Link>
                                        <Link href="/saude/faturas" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500">Faturamento (Dia 05)</Link>
                                        <Link href="/saude/reembolsos" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500">Pedidos de Reembolso</Link>
                                    </template>
                                    <template v-else>
                                        <Link v-if="hasServico('gestao_consultas')" href="/saude/agendamentos" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500">Agendamentos</Link>
                                        <Link v-if="hasServico('gestao_internacoes')" href="/saude/internacoes" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500">Internações Eletivas</Link>
                                        <Link href="/saude/prestadores" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500">Rede Credenciada</Link>
                                        <Link href="/saude/autorizacoes" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500">Autorizações</Link>
                                        <Link href="/saude/faturas" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500">Faturamento</Link>
                                        <Link v-if="hasServico('relatorios_gerenciais')" href="/saude/reembolsos" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500">Solicitações</Link>
                                    </template>
                                </div>
                            </div>

                            <!-- Relatórios -->
                            <Link v-if="$page.props.auth.permissions.includes('relatorios.view')" :href="route('relatorios.index')" @click="isMobileSidebarOpen = false" class="flex items-center gap-3 px-3 py-2 text-xs font-bold text-slate-600 dark:text-slate-400">
                                <ChartPieIcon class="w-5 h-5" />
                                <span>Relatórios</span>
                            </Link>

                            <!-- Ajustes -->
                            <div v-if="$page.props.auth.permissions.includes('configuracoes.users')" class="space-y-1">
                                <button @click="toggleGroup('ajustes')" class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold text-slate-600 dark:text-slate-400">
                                    <div class="flex items-center gap-3">
                                        <Cog6ToothIcon class="w-5 h-5" />
                                        <span>Ajustes</span>
                                    </div>
                                    <ChevronDownIcon class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': groupState.ajustes }" />
                                </button>
                                <div v-show="groupState.ajustes" class="pl-9 space-y-1">
                                    <Link href="/configuracoes" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500">Customização Visual</Link>
                                    <Link :href="route('configuracoes.pagamentos')" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500">Ajustes de Pagamentos</Link>
                                    <Link :href="route('configuracoes.contratos')" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500">Contratos Operacionais</Link>
                                    <Link :href="route('configuracoes.usuarios.index')" @click="isMobileSidebarOpen = false" class="block py-1.5 text-xs font-semibold text-slate-500">Gestão de Equipe</Link>
                                </div>
                            </div>
                        </template>
                    </nav>
                </aside>
            </Transition>

            <!-- MAIN CONTENT AREA -->
            <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8">
                <div class="w-full">
                    <!-- Page Title / Breadcrumb Area Slot -->
                    <div v-if="$slots.header" class="mb-4">
                        <slot name="header" />
                    </div>
                    
                    <!-- Main Page Content Slot -->
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

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-enter-active, .slide-leave-active { transition: transform 0.3s ease; }
.slide-enter-from, .slide-leave-to { transform: translateX(-100%); }
</style>
