<script setup>
import PrestadorLayout from '@/Layouts/PrestadorLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    UserCircleIcon,
    BuildingOffice2Icon,
    KeyIcon,
    CheckCircleIcon,
    MapPinIcon,
    TagIcon,
} from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    prestador: Object,
    usuario: Object,
});

const page = usePage();
const primaryColor = computed(() => page.props.tenant?.primary_color || '#2563eb');

// Formulário de dados do perfil
const profileForm = useForm({
    nome: props.prestador.nome,
    tipo: props.prestador.tipo,
    especialidades: props.prestador.especialidades || [],
    endereco: props.prestador.endereco || '',
    user_name: props.usuario.name,
    user_email: props.usuario.email,
});

const especialidadesText = computed({
    get: () => profileForm.especialidades.join(', '),
    set: (val) => { profileForm.especialidades = val.split(',').map(s => s.trim()).filter(s => s); }
});

const submitProfile = () => {
    profileForm.put(route('prestador.perfil.update'), {
        preserveScroll: true,
    });
};

// Formulário de senha
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submitPassword = () => {
    passwordForm.put(route('prestador.perfil.senha'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        }
    });
};

const tipoLabel = {
    'clinica': 'Clínica',
    'hospital': 'Hospital',
    'medico_pf': 'Médico (PF)',
    'laboratorio': 'Laboratório',
    'farmacia': 'Farmácia',
    'otica': 'Ótica',
    'loja': 'Loja / Comércio',
    'outro': 'Outro',
};
</script>

<template>
    <Head title="Meu Perfil" />

    <PrestadorLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 w-full">
                <div>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">Meu Perfil</h2>
                    <p class="text-[12px] text-[var(--text-muted)] mt-0.5 font-bold">
                        Gerencie suas informações e dados de acesso.
                    </p>
                </div>
                <div class="p-3 bg-[var(--bg-card)] rounded-xl border border-[var(--border-color)]">
                    <UserCircleIcon class="w-6 h-6 text-primary-dynamic" />
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12">

            <!-- Flash -->
            <div v-if="$page.props.flash?.success" class="bg-emerald-50 text-emerald-700 p-3.5 rounded-xl border border-emerald-100 flex items-center">
                <CheckCircleIcon class="w-5 h-5 mr-3 flex-shrink-0" />
                <span class="text-sm font-bold">{{ $page.props.flash.success }}</span>
            </div>

            <!-- Card: Dados do Estabelecimento -->
            <form @submit.prevent="submitProfile">
                <div class="zynix-card overflow-hidden">
                    <!-- Header -->
                    <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/30 border-b border-[var(--border-color)] flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-primary-dynamic/10 text-primary-dynamic flex items-center justify-center">
                            <BuildingOffice2Icon class="w-4 h-4" />
                        </div>
                        <h3 class="text-sm font-black text-[var(--text-heading)] uppercase tracking-widest">Dados do Estabelecimento</h3>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-5">
                        <div>
                            <InputLabel value="Nome / Razão Social *" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                            <TextInput v-model="profileForm.nome" type="text" class="mt-1.5 block w-full" placeholder="Ex: Clínica Odonto Vida" required />
                            <InputError :message="profileForm.errors.nome" class="mt-1" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <InputLabel value="Tipo *" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                                <select v-model="profileForm.tipo" class="mt-1.5 w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic py-2.5 px-3 text-sm font-medium" required>
                                    <option v-for="(label, key) in tipoLabel" :key="key" :value="key">{{ label }}</option>
                                </select>
                                <InputError :message="profileForm.errors.tipo" class="mt-1" />
                            </div>
                            <div>
                                <InputLabel value="Especialidades" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                                <TextInput v-model="especialidadesText" type="text" class="mt-1.5 block w-full" placeholder="Ex: Odonto, Ortodontia..." />
                                <p class="text-[10px] text-slate-400 mt-1 font-medium">Separe por vírgula</p>
                            </div>
                        </div>

                        <div>
                            <InputLabel value="Endereço" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                            <textarea v-model="profileForm.endereco" rows="2" class="mt-1.5 block w-full text-sm rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic py-2.5 px-3 font-medium resize-none" placeholder="Rua, número, bairro, cidade..."></textarea>
                            <InputError :message="profileForm.errors.endereco" class="mt-1" />
                        </div>
                    </div>

                    <!-- Separator: Dados de Acesso -->
                    <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/30 border-y border-[var(--border-color)] flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center">
                            <UserCircleIcon class="w-4 h-4" />
                        </div>
                        <h3 class="text-sm font-black text-[var(--text-heading)] uppercase tracking-widest">Dados de Acesso</h3>
                    </div>

                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <InputLabel value="Nome do Responsável *" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                                <TextInput v-model="profileForm.user_name" type="text" class="mt-1.5 block w-full" placeholder="Seu nome completo" required />
                                <InputError :message="profileForm.errors.user_name" class="mt-1" />
                            </div>
                            <div>
                                <InputLabel value="E-mail de Login *" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                                <TextInput v-model="profileForm.user_email" type="email" class="mt-1.5 block w-full" placeholder="seuemail@exemplo.com" required />
                                <InputError :message="profileForm.errors.user_email" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/30 border-t border-[var(--border-color)] flex justify-end">
                        <PrimaryButton type="submit" :disabled="profileForm.processing" class="!px-6 !py-2.5 shadow-lg shadow-primary-dynamic/20 text-xs">
                            {{ profileForm.processing ? 'Salvando...' : 'Salvar Alterações' }}
                        </PrimaryButton>
                    </div>
                </div>
            </form>

            <!-- Card: Alterar Senha -->
            <form @submit.prevent="submitPassword">
                <div class="zynix-card overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/30 border-b border-[var(--border-color)] flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center">
                            <KeyIcon class="w-4 h-4" />
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-[var(--text-heading)] uppercase tracking-widest">Alterar Senha</h3>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                            <div>
                                <InputLabel value="Senha Atual *" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                                <TextInput v-model="passwordForm.current_password" type="password" class="mt-1.5 block w-full" placeholder="••••••••" required />
                                <InputError :message="passwordForm.errors.current_password" class="mt-1" />
                            </div>
                            <div>
                                <InputLabel value="Nova Senha *" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                                <TextInput v-model="passwordForm.password" type="password" class="mt-1.5 block w-full" placeholder="Mín. 6 caracteres" required />
                                <InputError :message="passwordForm.errors.password" class="mt-1" />
                            </div>
                            <div>
                                <InputLabel value="Confirmar *" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                                <TextInput v-model="passwordForm.password_confirmation" type="password" class="mt-1.5 block w-full" placeholder="Repita a senha" required />
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/30 border-t border-[var(--border-color)] flex justify-end">
                        <PrimaryButton type="submit" :disabled="passwordForm.processing" class="!px-6 !py-2.5 !bg-amber-500 hover:!bg-amber-600 shadow-lg shadow-amber-500/20 text-xs">
                            {{ passwordForm.processing ? 'Alterando...' : 'Alterar Senha' }}
                        </PrimaryButton>
                    </div>
                </div>
            </form>

        </div>
    </PrestadorLayout>
</template>

<style scoped>
.text-primary-dynamic { color: v-bind(primaryColor); }
.bg-primary-dynamic { background-color: v-bind(primaryColor) !important; }
.border-primary-dynamic { border-color: v-bind(primaryColor) !important; }
</style>
