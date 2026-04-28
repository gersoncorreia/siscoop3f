<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { UserGroupIcon, ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline';

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
    <Head :title="tenant ? `Login - ${tenant.id}` : 'Portal da Cooperativa'" />

    <div class="min-h-screen bg-gray-50 flex flex-col md:flex-row font-sans selection:bg-primary-500 selection:text-white relative overflow-hidden">
        
        <!-- Lado Esquerdo - Painel Ilustrativo/Branding -->
        <div class="hidden md:flex md:w-1/2 bg-gradient-to-br from-primary-900 to-primary-700 relative items-center justify-center p-12">
            <!-- Pattern de Fundo -->
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0IiBoZWlnaHQ9IjQiPgo8cmVjdCB3aWR0aD0iNCIgaGVpZ2h0PSI0IiBmaWxsPSIjZmZmIiBmaWxsLW9wYWNpdHk9IjAuMDUiLz4KPC9zdmc+')] opacity-20"></div>
            
            <div class="relative z-10 max-w-lg text-white space-y-8">
                <div class="inline-flex items-center gap-3 bg-white/10 px-4 py-2 rounded-full border border-white/20 backdrop-blur-sm">
                    <UserGroupIcon class="w-6 h-6 text-green-300" />
                    <span class="font-semibold text-sm tracking-wide">Plataforma de Gestão Siscoop3F</span>
                </div>
                
                <h2 class="text-4xl lg:text-5xl font-bold leading-tight">
                    Bem-vindo(a) ao<br>Portal da sua<br><span class="text-green-300">{{ tenant ? tenant.id.toUpperCase() : 'Cooperativa' }}</span>
                </h2>
                
                <p class="text-lg text-primary-100 leading-relaxed border-l-4 border-green-400 pl-4">
                    Acompanhe seu capital, atualize seus dados e tenha acesso a todos os serviços da sua cooperativa de forma 100% digital e segura.
                </p>
                
                <div class="pt-8">
                    <!-- Placeholder Visual Simples -->
                    <div class="flex gap-4 opacity-70">
                        <div class="w-16 h-1 bg-white/30 rounded-full"></div>
                        <div class="w-8 h-1 bg-white/30 rounded-full"></div>
                        <div class="w-4 h-1 bg-white/30 rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lado Direito - Formulário de Login -->
        <div class="w-full md:w-1/2 flex items-center justify-center p-6 sm:p-12 lg:p-24 bg-white relative z-10 shadow-2xl md:shadow-none rounded-t-3xl md:rounded-l-3xl -mt-6 md:mt-0">
            <div class="w-full max-w-md space-y-8">
                
                <div class="md:hidden flex items-center gap-2 mb-8">
                    <UserGroupIcon class="w-10 h-10 text-primary-600" />
                    <span class="text-2xl font-black text-secondary-900 tracking-tight">Siscoop<span class="text-primary-600">3F</span></span>
                </div>

                <div class="text-center md:text-left">
                    <h1 class="text-3xl font-bold text-gray-900 flex flex-col md:block">
                        <span class="md:hidden text-lg text-gray-500 font-normal mb-1">Portal da {{ tenant ? tenant.id : 'Cooperativa' }}</span>
                        Acesso à Conta
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Digite seu e-mail e senha para entrar no seu painel.
                    </p>
                </div>

                <div v-if="status" class="mb-4 text-sm font-medium text-green-600 p-4 bg-green-50 rounded-xl border border-green-100">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="mt-8 space-y-6">
                    <div>
                        <InputLabel for="email" value="E-mail Pessoal ou Corporativo" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
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
                            <InputLabel for="password" value="Senha" />
                            <Link
                                v-if="canResetPassword"
                                href="/forgot-password"
                                class="text-sm font-semibold text-primary-600 hover:text-primary-500 transition-colors"
                            >
                                Esqueceu a senha?
                            </Link>
                        </div>
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="mt-4 flex items-center">
                        <label class="flex items-center group cursor-pointer">
                            <Checkbox name="remember" v-model:checked="form.remember" class="border-gray-300 rounded text-primary-600 focus:ring-primary-500" />
                            <span class="ms-2 text-sm text-gray-600 group-hover:text-gray-900">Manter conectado</span>
                        </label>
                    </div>

                    <div class="mt-8">
                        <PrimaryButton
                            class="w-full !py-4 shadow-xl"
                            :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                            :disabled="form.processing"
                        >
                            <ArrowRightOnRectangleIcon class="w-5 h-5 mr-2" />
                            <span>{{ form.processing ? 'Entrando...' : 'Entrar na Conta' }}</span>
                        </PrimaryButton>
                    </div>
                </form>
                
                <p class="mt-10 text-center text-sm text-gray-500">
                    Ainda não é cooperado? 
                    <a href="/adesao" class="font-semibold leading-6 text-primary-600 hover:text-primary-500 transition-colors">Solicite sua adesão online</a>
                </p>
            </div>
        </div>
    </div>
</template>
