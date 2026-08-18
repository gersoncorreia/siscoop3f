<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    ChevronLeftIcon,
    UserIcon,
    ShieldCheckIcon,
    KeyIcon,
    FingerPrintIcon,
    LockClosedIcon,
    InformationCircleIcon
} from '@heroicons/vue/24/outline';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    user_edit: {
        type: Object,
        default: null
    },
    roles: Array,
    permissions: Array,
});

const form = useForm({
    name: props.user_edit?.name ?? '',
    email: props.user_edit?.email ?? '',
    password: '',
    password_confirmation: '',
    role: props.user_edit?.roles[0]?.name ?? '',
    permissions: props.user_edit?.permissions.map(p => p.name) ?? [],
});

const submit = () => {
    if (props.user_edit) {
        form.put(route('configuracoes.usuarios.update', props.user_edit.id));
    } else {
        form.post(route('configuracoes.usuarios.store'));
    }
};

const togglePermission = (name) => {
    const index = form.permissions.indexOf(name);
    if (index > -1) {
        form.permissions.splice(index, 1);
    } else {
        form.permissions.push(name);
    }
};

// Agrupar permissões por categoria para facilitar a visualização
const groupedPermissions = computed(() => {
    const groups = {};
    props.permissions.forEach(p => {
        const [category] = p.name.split('.');
        if (!groups[category]) groups[category] = [];
        groups[category].push(p);
    });
    return groups;
});

const isRestrictedUser = computed(() => {
    if (!props.user_edit) return false;
    const roleName = props.user_edit.roles?.[0]?.name;
    return ['Associado', 'Prestador'].includes(roleName);
});
</script>

<template>
    <Head :title="user_edit ? 'Editar Usuário' : 'Novo Usuário'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <Breadcrumbs :items="[
                        { label: 'Configurações', href: route('configuracoes.index') }, 
                        { label: 'Usuários', href: route('configuracoes.usuarios.index') },
                        { label: user_edit ? 'Editar' : 'Novo' }
                    ]" />
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">
                        {{ user_edit ? 'Ajustar Acessos' : 'Credenciar Funcionário' }}
                    </h2>
                </div>
                <Link :href="route('configuracoes.usuarios.index')">
                    <SecondaryButton class="!rounded-full !px-5 !py-2.5 !text-[10px] !font-black !uppercase !tracking-widest">
                        <ChevronLeftIcon class="w-4 h-4 mr-1.5" />
                        Lista de Usuários
                    </SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <form @submit.prevent="submit" class="space-y-8">
                    <!-- Informações Básicas -->
                    <div class="zynix-card overflow-hidden !p-0">
                        <div class="p-8 border-b border-[var(--border-color)] bg-slate-50/50 dark:bg-slate-800/20">
                            <div class="flex items-center">
                                <div class="p-3 rounded-2xl bg-indigo-500/10 text-indigo-500">
                                    <UserIcon class="w-8 h-8" />
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">Dados de Identificação</h3>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Nome e e-mail de acesso institucional</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-8 space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-1">
                                    <InputLabel for="name" value="Nome Completo" />
                                    <TextInput id="name" v-model="form.name" type="text" class="w-full" required />
                                    <InputError :message="form.errors.name" />
                                </div>

                                <div class="space-y-1">
                                    <InputLabel for="email" value="E-mail Institucional" />
                                    <TextInput id="email" v-model="form.email" type="email" class="w-full" required />
                                    <InputError :message="form.errors.email" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-1">
                                    <InputLabel for="password" :value="user_edit ? 'Nova Senha (deixe em branco para manter)' : 'Senha de Acesso'" />
                                    <TextInput id="password" v-model="form.password" type="password" class="w-full" :required="!user_edit" />
                                    <InputError :message="form.errors.password" />
                                </div>

                                <div class="space-y-1">
                                    <InputLabel for="password_confirmation" value="Confirmar Senha" />
                                    <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" class="w-full" :required="!user_edit" />
                                    <InputError :message="form.errors.password_confirmation" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Role Info for Restricted Users -->
                    <div v-if="isRestrictedUser" class="zynix-card overflow-hidden !p-0">
                        <div class="p-8 border-b border-[var(--border-color)] bg-slate-50/50 dark:bg-slate-800/20">
                            <div class="flex items-center">
                                <div class="p-3 rounded-2xl bg-amber-500/10 text-amber-500">
                                    <ShieldCheckIcon class="w-8 h-8" />
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">Perfil de Função Restrito</h3>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Usuário externo: {{ user_edit.roles[0].name }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-8">
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 leading-relaxed">
                                Este usuário possui um perfil específico do sistema (<strong>{{ user_edit.roles[0].name }}</strong>). 
                                Permissões adicionais ou alterações de perfil não são permitidas para evitar conflitos nas regras de acesso do portal.
                            </p>
                        </div>
                    </div>

                    <!-- Definição de Perfil (Role) -->
                    <div v-if="!isRestrictedUser" class="zynix-card overflow-hidden !p-0">
                        <div class="p-8 border-b border-[var(--border-color)] bg-slate-50/50 dark:bg-slate-800/20">
                            <div class="flex items-center">
                                <div class="p-3 rounded-2xl bg-primary-dynamic/10 text-primary-dynamic">
                                    <ShieldCheckIcon class="w-8 h-8" />
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">Perfil de Função</h3>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Papel principal do colaborador na cooperativa</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-8">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <label 
                                    v-for="role in roles" 
                                    :key="role.id"
                                    class="relative flex flex-col p-4 cursor-pointer focus:outline-none rounded-2xl border-2 transition-all"
                                    :class="form.role === role.name 
                                        ? 'border-primary-dynamic bg-primary-dynamic/5 ring-1 ring-primary-dynamic' 
                                        : 'border-[var(--border-color)] bg-[var(--bg-card)] hover:border-slate-300 dark:hover:border-slate-600'"
                                >
                                    <input type="radio" name="role" :value="role.name" v-model="form.role" class="sr-only" />
                                    <span class="flex items-center justify-between mb-1">
                                        <span class="text-[11px] font-black uppercase tracking-widest text-[var(--text-heading)]">
                                            {{ role.name }}
                                        </span>
                                        <ShieldCheckIcon v-if="form.role === role.name" class="w-4 h-4 text-primary-dynamic" />
                                    </span>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight leading-tight">
                                        {{ role.name === 'Presidente' ? 'Acesso total' : 'Acesso restrito por departamento' }}
                                    </p>
                                </label>
                            </div>
                            <InputError :message="form.errors.role" class="mt-4" />
                        </div>
                    </div>

                    <!-- Permissões Granulares -->
                    <div v-if="!isRestrictedUser" class="zynix-card overflow-hidden !p-0">
                        <div class="p-8 border-b border-[var(--border-color)] bg-slate-50/50 dark:bg-slate-800/20">
                            <div class="flex items-center">
                                <div class="p-3 rounded-2xl bg-amber-500/10 text-amber-500">
                                    <KeyIcon class="w-8 h-8" />
                                </div>
                                <div class="ml-4">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">Habilitação Granular</h3>
                                        <div class="bg-amber-100 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-widest">Opcional</div>
                                    </div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Ative permissões específicas além do perfil base</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-8">
                            <div class="space-y-10">
                                <div v-for="(perms, category) in groupedPermissions" :key="category">
                                    <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 border-b border-[var(--border-color)] pb-2 flex items-center">
                                        <InformationCircleIcon class="w-4 h-4 mr-2" />
                                        Módulo: {{ category }}
                                    </h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-y-4 gap-x-8">
                                        <label v-for="permission in perms" :key="permission.id" class="flex items-center group cursor-pointer">
                                            <Checkbox 
                                                v-model:checked="form.permissions" 
                                                :value="permission.name" 
                                                class="w-5 h-5 !rounded-lg border-2 border-[var(--border-color)] text-primary-dynamic focus:ring-primary-dynamic"
                                            />
                                            <span class="ml-3">
                                                <span class="block text-[11px] font-black uppercase tracking-widest text-[var(--text-main)] group-hover:text-primary-dynamic transition-colors">
                                                    {{ permission.name.split('.')[1] }}
                                                </span>
                                                <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-tighter">
                                                    {{ permission.name }}
                                                </span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end space-x-4 pb-12">
                        <Link :href="route('configuracoes.usuarios.index')">
                            <SecondaryButton class="!px-10 !py-4 !rounded-full !text-[11px] !font-black !uppercase !tracking-widest">Descartar</SecondaryButton>
                        </Link>
                        <PrimaryButton :disabled="form.processing" class="!px-12 !py-4 !rounded-full !text-[11px] !font-black !uppercase !tracking-widest shadow-2xl shadow-primary-dynamic/30 transition-all hover:scale-105 active:scale-95">
                            <FingerPrintIcon class="w-4 h-4 mr-2" />
                            {{ user_edit ? 'Salvar Alterações' : 'Efetivar Cadastro' }}
                        </PrimaryButton>
                    </div>
                </form>
        </div>
    </AuthenticatedLayout>
</template>
