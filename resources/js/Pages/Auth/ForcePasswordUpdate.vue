<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { LockClosedIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline';

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update.temporary'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Atualizar Acesso" />

    <div class="min-h-screen bg-[var(--bg-main)] flex items-center justify-center p-6 transition-colors duration-300">
        <!-- Background Decor -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
            <div class="absolute -top-[10%] -left-[10%] w-[50%] h-[50%] bg-primary-dynamic/10 blur-[150px] rounded-full"></div>
            <div class="absolute top-[20%] right-[10%] w-[30%] h-[30%] bg-rose-500/5 blur-[120px] rounded-full"></div>
        </div>

        <div class="relative w-full max-w-md z-10">
            <div class="bg-[var(--bg-card)] p-10 rounded-[2.5rem] border border-[var(--border-color)] shadow-2xl">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-primary-dynamic/10 text-primary-dynamic rounded-full flex items-center justify-center mx-auto mb-6">
                        <LockClosedIcon class="w-8 h-8" />
                    </div>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tighter">
                        Primeiro Acesso
                    </h2>
                    <p class="text-sm text-slate-500 mt-2 font-medium">
                        Para garantir a segurança da sua conta, por favor, defina uma nova senha de acesso.
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Current Password -->
                    <div>
                        <InputLabel for="current_password" value="Código de Acesso Temporário (Recebido no E-mail)" class="!font-black text-[10px] uppercase tracking-widest text-[var(--text-heading)]"/>
                        <TextInput
                            id="current_password"
                            type="password"
                            class="mt-1 block w-full !bg-slate-50 dark:!bg-slate-900 !border-slate-200 dark:!border-slate-800 focus:!border-primary-dynamic focus:!ring-primary-dynamic rounded-xl !py-3"
                            v-model="form.current_password"
                            required
                            placeholder="****"
                            autocomplete="current-password"
                        />
                        <InputError class="mt-2" :message="form.errors.current_password" />
                    </div>

                    <!-- New Password -->
                    <div>
                        <InputLabel for="password" value="Nova Senha Definitiva" class="!font-black text-[10px] uppercase tracking-widest text-[var(--text-heading)]"/>
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full !bg-slate-50 dark:!bg-slate-900 !border-slate-200 dark:!border-slate-800 focus:!border-primary-dynamic focus:!ring-primary-dynamic rounded-xl !py-3"
                            v-model="form.password"
                            required
                            placeholder="Mínimo de 8 caracteres"
                            autocomplete="new-password"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <InputLabel for="password_confirmation" value="Confirmar Nova Senha" class="!font-black text-[10px] uppercase tracking-widest text-[var(--text-heading)]"/>
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="mt-1 block w-full !bg-slate-50 dark:!bg-slate-900 !border-slate-200 dark:!border-slate-800 focus:!border-primary-dynamic focus:!ring-primary-dynamic rounded-xl !py-3"
                            v-model="form.password_confirmation"
                            required
                            placeholder="Repita a nova senha"
                            autocomplete="new-password"
                        />
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>

                    <div class="pt-4">
                        <PrimaryButton 
                            class="w-full justify-center !py-4 rounded-xl text-xs flex gap-2 disabled:!opacity-50" 
                            :class="{ 'opacity-25': form.processing }" 
                            :disabled="form.processing"
                        >
                            <ShieldCheckIcon class="w-5 h-5" />
                            <span>{{ form.processing ? 'Configurando...' : 'Confirmar Acesso Seguro' }}</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
:root {
    --bg-main: #f8fafc;
    --bg-card: #ffffff;
    --border-color: rgba(226, 232, 240, 0.8);
    --text-heading: #0f172a;
}
.dark {
    --bg-main: #0f1014;
    --bg-card: #1b1c22;
    --border-color: rgba(255, 255, 255, 0.05);
    --text-heading: #f8fafc;
}
</style>
