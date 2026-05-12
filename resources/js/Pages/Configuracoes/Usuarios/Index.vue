<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    PlusIcon, 
    PencilSquareIcon, 
    TrashIcon, 
    UserIcon,
    MagnifyingGlassIcon,
    ShieldCheckIcon,
} from '@heroicons/vue/24/outline';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    users: Object,
});

const form = useForm({});

function deleteUser(id) {
    if (window.confirm('Tem certeza que deseja excluir este usuário?')) {
        form.delete(route('configuracoes.usuarios.destroy', id), {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <Head title="Gestão de Usuários" />

    <AuthenticatedLayout>
        <div class="py-6 sm:py-8 lg:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-10">
                    <div>
                        <Breadcrumbs :items="[{ label: 'Configurações', href: route('configuracoes.index') }, { label: 'Usuários' }]" />
                        <h1 class="text-2xl lg:text-3xl font-black text-[var(--text-heading)] tracking-tighter mt-2">
                            Gestão de Acessos
                        </h1>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">Gerencie os funcionários e permissões da cooperativa</p>
                    </div>
                    <div>
                        <Link :href="route('configuracoes.usuarios.create')">
                            <PrimaryButton class="shadow-xl">
                                <PlusIcon class="w-4 h-4 mr-2" />
                                Cadastrar Usuário
                            </PrimaryButton>
                        </Link>
                    </div>
                </div>

                <div class="zynix-table-wrapper">
                    <!-- Filters -->
                    <div class="zynix-list-header flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="relative flex-grow max-w-md">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                <MagnifyingGlassIcon class="w-4 h-4 text-slate-400" />
                            </div>
                            <input 
                                type="text" 
                                placeholder="Buscar usuários..." 
                                class="block w-full py-3 pl-12 pr-4 text-[11px] font-black uppercase tracking-widest bg-slate-100 dark:bg-slate-800/50 border-none rounded-full text-[var(--text-main)] placeholder:text-slate-400 focus:ring-1 focus:ring-primary-dynamic transition-all"
                            />
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="zynix-table w-full">
                            <thead>
                                <tr>
                                    <th class="pl-8">Usuário</th>
                                    <th class="text-center">E-mail</th>
                                    <th class="text-center">Perfil / Role</th>
                                    <th class="text-center">Permissões Extras</th>
                                    <th class="text-right pr-8">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users.data" :key="user.id">
                                    <td class="pl-8">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-9 h-9 p-2 rounded-xl bg-indigo-500/10 text-indigo-500 shadow-inner">
                                                <UserIcon class="w-full h-full" />
                                            </div>
                                            <div class="ml-4">
                                                <div class="font-black text-[var(--text-heading)] tracking-tight">{{ user.name }}</div>
                                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">ID #{{ user.id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center text-xs font-medium">
                                        {{ user.email }}
                                    </td>
                                    <td class="text-center">
                                        <div class="flex flex-wrap justify-center gap-1">
                                            <span 
                                                v-for="role in user.roles" 
                                                :key="role.id"
                                                class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-full inline-flex items-center border"
                                                :class="['Associado', 'Prestador'].includes(role.name) 
                                                    ? 'bg-amber-500/10 text-amber-500 border-amber-500/20' 
                                                    : 'bg-primary-dynamic/10 text-primary-dynamic border-primary-dynamic/20'"
                                            >
                                                {{ role.name }}
                                            </span>
                                        </div>
                                        <div v-if="user.roles.some(r => ['Associado', 'Prestador'].includes(r.name))" class="mt-1">
                                            <span class="text-[8px] font-black uppercase tracking-[0.2em] text-slate-400">Usuário do Portal</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span v-if="!user.roles.some(r => ['Associado', 'Prestador'].includes(r.name))" class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                            {{ user.permissions?.length || 0 }} ativas
                                        </span>
                                        <span v-else class="text-[10px] font-black text-slate-300 uppercase tracking-widest italic">
                                            Restrito
                                        </span>
                                    </td>
                                    <td class="text-right pr-8 whitespace-nowrap">
                                        <div class="flex items-center justify-end space-x-1">
                                            <Link 
                                                :href="route('configuracoes.usuarios.edit', user.id)"
                                                class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-xl transition-all"
                                                title="Editar"
                                            >
                                                <PencilSquareIcon class="w-4 h-4" />
                                            </Link>
                                            <button 
                                                v-if="$page.props.auth.user.id !== user.id && !user.roles.some(r => ['Associado', 'Prestador'].includes(r.name))"
                                                @click="deleteUser(user.id)" 
                                                class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-all"
                                                title="Excluir"
                                            >
                                                <TrashIcon class="w-4 h-4" />
                                            </button>
                                            <div v-else-if="user.roles.some(r => ['Associado', 'Prestador'].includes(r.name))" class="p-2 text-slate-300 cursor-not-allowed" title="Gestão via Módulo Específico">
                                                <TrashIcon class="w-4 h-4" />
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
