<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { UserGroupIcon, ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline';
import { computed } from 'vue';

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    tenant: {
        type: Object,
        default: null,
    }
});

const primaryColor = computed(() => props.tenant?.primary_color || '#2563eb');
const logoUrl = computed(() => props.tenant?.logo_url || null);
const entityName = computed(() => props.tenant?.name || props.tenant?.nome || props.tenant?.id || 'Entidade');

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head :title="tenant ? `Login - ${entityName}` : 'Portal da Entidade'" />

    <div 
        class="min-h-screen bg-gray-50 flex flex-col md:flex-row font-sans relative overflow-hidden"
        :style="{ '--primary-dynamic': primaryColor }"
    >
        <!-- Lado Esquerdo - Painel Ilustrativo/Branding -->
        <div 
            class="hidden md:flex md:w-1/2 relative items-center justify-center p-12 overflow-hidden"
            :style="{ background: `linear-gradient(135deg, ${primaryColor}, #0f172a)` }"
        >
            <!-- Pattern de Fundo -->
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0IiBoZWlnaHQ9IjQiPgo8cmVjdCB3aWR0aD0iNCIgaGVpZ2h0PSI0IiBmaWxsPSIjZmZmIiBmaWxsLW9wYWNpdHk9IjAuMDUiLz4KPC9zdmc+')] opacity-20"></div>
            
            <div class="relative z-10 max-w-lg text-white space-y-8 w-full">
                <!-- Badge Plataforma -->
                <div class="inline-flex items-center gap-3 bg-white/10 px-4 py-2 rounded-full border border-white/20 backdrop-blur-sm">
                    <UserGroupIcon class="w-5 h-5 text-white/80" />
                    <span class="font-semibold text-xs tracking-wide uppercase">Plataforma de Gestão Siscoop3F</span>
                </div>

                <!-- Logomarca da Entidade (Área Destacada) -->
                <div v-if="logoUrl" class="py-2">
                    <img :src="logoUrl" :alt="entityName" class="max-h-20 w-auto object-contain drop-shadow-xl rounded-lg bg-white/10 p-2 border border-white/20 backdrop-blur-md" />
                </div>
                
                <!-- Título Adaptativo -->
                <h2 class="text-4xl lg:text-5xl font-black leading-tight tracking-tight">
                    <template v-if="logoUrl">
                        Bem-vindo(a) ao<br>Portal
                    </template>
                    <template v-else>
                        Bem-vindo(a) ao<br>Portal da sua<br><span class="text-white/90 underline decoration-white/30 uppercase text-3xl lg:text-4xl font-extrabold">{{ entityName }}</span>
                    </template>
                </h2>
                
                <p class="text-sm lg:text-base text-white/80 leading-relaxed border-l-4 border-white/40 pl-4">
                    Acompanhe seu capital, atualize seus dados e tenha acesso a todos os serviços de forma 100% digital e segura.
                </p>
                
                <div class="pt-4">
                    <div class="flex gap-4 opacity-70">
                        <div class="w-16 h-1 bg-white/40 rounded-full"></div>
                        <div class="w-8 h-1 bg-white/30 rounded-full"></div>
                        <div class="w-4 h-1 bg-white/20 rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lado Direito - Formulário de Login -->
        <div class="w-full md:w-1/2 flex items-center justify-center p-6 sm:p-12 lg:p-24 bg-white relative z-10 shadow-2xl md:shadow-none rounded-t-3xl md:rounded-l-3xl -mt-6 md:mt-0">
            <div class="w-full max-w-md space-y-8">
                
                <!-- Mobile Header Logo/Name -->
                <div class="md:hidden flex items-center gap-3 mb-6">
                    <img v-if="logoUrl" :src="logoUrl" :alt="entityName" class="max-h-12 w-auto object-contain" />
                    <div v-else class="flex items-center gap-2">
                        <UserGroupIcon class="w-8 h-8 text-[var(--primary-dynamic)]" />
                        <span class="text-xl font-black text-slate-900 tracking-tight">{{ entityName }}</span>
                    </div>
                </div>

                <div class="text-center md:text-left">
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight flex flex-col md:block">
                        <span class="md:hidden text-xs text-slate-400 uppercase tracking-widest font-bold mb-1">Portal {{ entityName }}</span>
                        Acesso à Conta
                    </h1>
                    <p class="mt-2 text-xs font-medium text-slate-500">
                        Digite seu e-mail e senha para entrar no seu painel.
                    </p>
                </div>

                <div v-if="status" class="mb-4 text-sm font-medium text-emerald-600 p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="mt-8 space-y-6">
                    <div>
                        <InputLabel for="email" value="E-mail Pessoal ou Corporativo" class="!text-[10px] !font-black uppercase tracking-widest text-slate-500" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-2 block w-full rounded-xl border-slate-200 shadow-sm focus:border-[var(--primary-dynamic)] focus:ring-[var(--primary-dynamic)] py-3 px-4"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="seu.email@exemplo.com"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="mt-4">
                        <div class="flex items-center justify-between">
                            <InputLabel for="password" value="Senha" class="!text-[10px] !font-black uppercase tracking-widest text-slate-500" />
                            <Link
                                v-if="canResetPassword"
                                href="/forgot-password"
                                class="text-xs font-bold text-[var(--primary-dynamic)] hover:brightness-110 transition-colors"
                            >
                                Esqueceu a senha?
                            </Link>
                        </div>
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-2 block w-full rounded-xl border-slate-200 shadow-sm focus:border-[var(--primary-dynamic)] focus:ring-[var(--primary-dynamic)] py-3 px-4"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="mt-4 flex items-center">
                        <label class="flex items-center group cursor-pointer">
                            <Checkbox name="remember" v-model:checked="form.remember" class="border-slate-300 rounded text-[var(--primary-dynamic)] focus:ring-[var(--primary-dynamic)]" />
                            <span class="ms-2 text-xs font-semibold text-slate-600 group-hover:text-slate-900">Manter conectado</span>
                        </label>
                    </div>

                    <div class="mt-8">
                        <PrimaryButton
                            class="w-full !py-4 shadow-xl !bg-[var(--primary-dynamic)] hover:brightness-110 border-none text-white font-bold !rounded-xl"
                            :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                            :disabled="form.processing"
                        >
                            <ArrowRightOnRectangleIcon class="w-5 h-5 mr-2" />
                            <span>{{ form.processing ? 'Entrando...' : 'ENTRAR NA CONTA' }}</span>
                        </PrimaryButton>
                    </div>
                </form>
                
                <p class="mt-10 text-center text-xs text-slate-500 font-medium">
                    Ainda não é associado/cadastrado? 
                    <a href="/adesao" class="font-bold text-[var(--primary-dynamic)] hover:underline transition-colors">Solicite sua adesão online</a>
                </p>
            </div>
        </div>
    </div>
</template>
