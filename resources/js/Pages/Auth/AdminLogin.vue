<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ShieldCheckIcon } from '@heroicons/vue/24/outline';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('master-admin.login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Controle Central Login" />

    <div class="min-h-screen bg-gradient-to-br from-secondary-800 via-secondary-700 to-gray-900 text-gray-100 flex items-center justify-center p-4 selection:bg-primary-500">
        
        <!-- Animated Background Elements -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none blur-3xl opacity-20 z-0">
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-primary-600 animate-[pulse_10s_ease-in-out_infinite]"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-indigo-600 animate-[pulse_12s_ease-in-out_infinite_reverse]"></div>
        </div>

        <div class="w-full max-w-md bg-secondary-900/90 backdrop-blur-xl border border-white/10 rounded-3xl shadow-2xl overflow-hidden relative z-10">
            <!-- Header Brand -->
            <div class="flex flex-col items-center justify-center pt-10 pb-6 border-b border-white/5 bg-black/20">
                <ShieldCheckIcon class="h-12 w-12 text-primary-500 mb-4" />
                <h1 class="text-2xl font-black text-white tracking-tight">Siscoop<span class="text-primary-500">3F</span></h1>
                <p class="text-sm font-semibold text-gray-200 mt-1 uppercase tracking-widest">Controle Central Master</p>
            </div>

            <!-- Login Form -->
            <div class="p-8 sm:p-10">
                <div v-if="status" class="mb-4 text-sm font-medium text-green-400 p-3 bg-green-900/30 rounded-lg border border-green-800">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="email" value="E-mail Administrativo" class="text-white font-medium" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full bg-white/10 border-white/20 text-white placeholder-gray-400 focus:border-primary-500 focus:ring-primary-500"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="admin@siscoop3f.com.br"
                        />
                        <InputError class="mt-2 text-red-400" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Senha" class="text-white font-medium" />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full bg-white/10 border-white/20 text-white placeholder-gray-400 focus:border-primary-500 focus:ring-primary-500"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                        />
                        <InputError class="mt-2 text-red-400" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between mt-6">
                        <label class="flex items-center">
                            <Checkbox name="remember" v-model:checked="form.remember" class="bg-white/10 border-white/20 checked:bg-primary-500 focus:ring-primary-500" />
                            <span class="ms-2 text-sm text-white font-medium">Lembrar-me</span>
                        </label>
                    </div>

                    <div class="mt-8">
                        <PrimaryButton
                            class="w-full justify-center bg-primary-600 hover:bg-primary-500 text-white py-3 rounded-xl shadow-lg shadow-primary-900/20 transition-all font-bold text-base"
                            :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing">Autenticando...</span>
                            <span v-else>Acessar Painel Master</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
            
            <!-- Secure Footer -->
            <div class="bg-black/20 p-4 border-t border-white/5 text-center">
                <p class="text-xs text-gray-200 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    Acesso Exclusivo à Administração do Sistema
                </p>
            </div>
        </div>
    </div>
</template>
