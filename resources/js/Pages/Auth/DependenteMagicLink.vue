<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { 
    KeyIcon, 
    ShieldCheckIcon,
    LockClosedIcon,
    BuildingOfficeIcon,
    ArrowRightIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    token: String,
    dependenteNome: String,
    associadoNome: String,
    tenant: Object,
});

const form = useForm({
    cpf_5_digitos: '',
});

const submit = () => {
    form.post(route('dependente.magic-link.validate', props.token));
};
</script>

<template>
    <Head title="Validação de Acesso Mágico" />

    <div class="min-h-screen flex flex-col justify-center items-center p-4 bg-gradient-to-b from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-950 font-sans">
        <div class="w-full max-w-md">
            <!-- Header Brand -->
            <div class="text-center mb-8">
                <div class="inline-flex p-4 rounded-3xl bg-primary-dynamic/10 text-primary-dynamic shadow-inner mb-4">
                    <BuildingOfficeIcon class="w-10 h-10" />
                </div>
                <h1 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">{{ tenant?.nome || 'Fundação Santa Casa' }}</h1>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Portal de Acesso do Dependente</p>
            </div>

            <!-- Card de Autenticação -->
            <div class="zynix-card p-8 shadow-2xl relative overflow-hidden">
                <div class="mb-6 pb-6 border-b border-[var(--border-color)]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center font-black">
                            <KeyIcon class="w-5 h-5" />
                        </div>
                        <div>
                            <h2 class="text-base font-black text-[var(--text-heading)]">Olá, {{ dependenteNome }}!</h2>
                            <p class="text-[11px] text-slate-500">Dependente vinculado a <strong>{{ associadoNome }}</strong></p>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-primary-dynamic/5 rounded-2xl border border-primary-dynamic/10 mb-6">
                    <p class="text-xs text-slate-600 dark:text-slate-300 font-medium leading-relaxed flex items-start gap-2">
                        <LockClosedIcon class="w-4 h-4 text-primary-dynamic shrink-0 mt-0.5" />
                        <span>Por motivos de segurança, informe os <strong>5 primeiros dígitos do seu CPF</strong> para liberar o acesso ao seu painel.</span>
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="cpf_5_digitos" value="5 Primeiros Dígitos do CPF:" class="!text-[11px] !font-black uppercase tracking-widest" />
                        <TextInput 
                            id="cpf_5_digitos"
                            v-model="form.cpf_5_digitos"
                            type="text"
                            maxlength="5"
                            placeholder="Ex: 12345"
                            class="w-full text-center font-mono text-xl font-bold tracking-[0.3em] py-3.5 mt-2"
                            required
                            autofocus
                        />
                        <InputError :message="form.errors.cpf_5_digitos" class="mt-2" />
                    </div>

                    <PrimaryButton 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full justify-center !py-4 !rounded-full !text-[11px] !font-black !uppercase !tracking-widest shadow-xl shadow-primary-dynamic/20"
                    >
                        <span v-if="!form.processing" class="flex items-center gap-2">
                            Acessar Meu Painel
                            <ArrowRightIcon class="w-4 h-4" />
                        </span>
                        <span v-else>Validando CPF...</span>
                    </PrimaryButton>
                </form>
            </div>

            <!-- Footer Lock Notice -->
            <div class="text-center mt-6 text-[10px] text-slate-400 font-bold uppercase tracking-widest flex items-center justify-center gap-1.5">
                <ShieldCheckIcon class="w-4 h-4 text-emerald-500" />
                Conexão Criptografada e Segura — Fundação Santa Casa da Amazônia
            </div>
        </div>
    </div>
</template>

<style scoped>
</style>
