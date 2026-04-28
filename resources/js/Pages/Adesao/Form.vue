<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import InputError from '@/Components/InputError.vue';
import { 
    UserIcon, 
    UserGroupIcon, 
    ArrowRightIcon, 
    CheckCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    tenant: Object
});

const primaryColor = computed(() => props.tenant?.primary_color || '#2563eb');

const form = useForm({
    nome: '',
    cpf: '',
    email: '',
    matricula: '',
    telefones: [
        { numero: '', tipo: 'celular' }
    ],
});

// CPF Masking logic
function formatCPF(value) {
    let v = value.replace(/\D/g, '');
    if (v.length > 11) v = v.substring(0, 11);
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    return v;
}

function handleCpfInput(event) {
    form.cpf = formatCPF(event.target.value);
}

function submitForm() {
    form.post(route('adesao.store'), {
        onSuccess: () => {
            // Vue inertia handles redirect to success
        }
    });
}
</script>

<template>
    <Head title="Adesão Digital" />

    <GuestLayout fullWidth>
        <div class="flex flex-col lg:grid lg:grid-cols-12 lg:h-screen lg:overflow-hidden">
            <!-- Coluna Esquerda: Branding & Boas-vindas -->
            <div class="lg:col-span-5 bg-primary-dynamic/[0.08] dark:bg-primary-dynamic/5 border-b lg:border-b-0 lg:border-r border-[var(--border-color)]/20 p-8 lg:p-16 flex flex-col justify-between relative overflow-hidden lg:h-full">
                <!-- Background Decoration -->
                <div class="absolute -top-24 -left-24 w-64 h-64 bg-primary-dynamic/5 rounded-full blur-3xl"></div>
                
                <div class="relative z-10 w-full">
                    <div class="flex items-center justify-between mb-12">
                        <div class="inline-flex p-3 rounded-2xl bg-primary-dynamic/10 text-primary-dynamic shadow-inner border border-primary-dynamic/5">
                            <img v-if="tenant?.logo_url" :src="tenant.logo_url" class="h-10 w-auto object-contain" />
                            <UserGroupIcon v-else class="h-8 w-8" />
                        </div>
                        <ThemeToggle />
                    </div>

                    <div class="space-y-6">
                        <h1 class="text-4xl lg:text-5xl font-black text-[var(--text-heading)] tracking-tighter leading-none">
                            Solicitação de <span class="text-primary-dynamic">Adesão</span>
                        </h1>
                        <p class="text-[var(--text-muted)] text-lg font-medium leading-relaxed max-w-sm pt-2">
                            Preencha seus dados básicos e realize o pedido de associação à <strong class="text-[var(--text-heading)]">{{ tenant?.nome || 'nossa Cooperativa' }}</strong>.
                        </p>
                    </div>

                    <div class="mt-16 space-y-6">
                        <div class="flex items-center gap-4 group">
                            <div class="w-12 h-12 rounded-2xl bg-white dark:bg-slate-800 flex items-center justify-center shadow-sm border border-[var(--border-color)]/20 text-primary-dynamic">
                                <CheckCircleIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <span class="block text-sm font-black text-[var(--text-heading)] tracking-tight">Cadastro Rápido</span>
                                <span class="text-xs font-medium text-[var(--text-muted)]">Apenas os contatos iniciais.</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 group">
                            <div class="w-12 h-12 rounded-2xl bg-white dark:bg-slate-800 flex items-center justify-center shadow-sm border border-[var(--border-color)]/20 text-primary-dynamic">
                                <CheckCircleIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <span class="block text-sm font-black text-[var(--text-heading)] tracking-tight">Aprovação Segura</span>
                                <span class="text-xs font-medium text-[var(--text-muted)]">Acesso liberado após a análise.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 pt-12 pb-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] opacity-40">
                        &copy; 2024 {{ tenant?.nome || 'Siscoop3F' }}
                    </p>
                </div>
            </div>

            <!-- Coluna Direita: Formulário -->
            <div class="lg:col-span-7 bg-[var(--bg-card)] p-6 lg:p-16 lg:overflow-y-auto lg:h-full relative py-12">
                <div class="max-w-2xl mx-auto">
                    <form @submit.prevent="submitForm" class="space-y-10">
                        
                        <div class="flex items-center gap-3 mb-6 border-b border-[var(--border-color)]/30 pb-6">
                            <div class="w-12 h-12 rounded-[1rem] bg-primary-dynamic/10 flex items-center justify-center text-primary-dynamic shadow-inner">
                                <UserIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-[var(--text-heading)] tracking-tighter">Dados Iniciais</h3>
                                <p class="text-[11px] uppercase tracking-widest font-bold text-slate-400 mt-1">Contato do proponente</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="md:col-span-2 space-y-2">
                                <InputLabel for="nome" value="Nome Completo" class="!font-black text-[10px] uppercase tracking-widest text-[var(--text-heading)]" />
                                <TextInput 
                                    id="nome" v-model="form.nome" type="text" 
                                    class="block w-full !bg-slate-50 dark:!bg-slate-900/50 !py-4 rounded-xl" 
                                    placeholder="Ex: João Silva Santos"
                                    required autofocus 
                                />
                                <InputError class="mt-2" :message="form.errors.nome" />
                            </div>

                            <div class="space-y-2">
                                <InputLabel for="cpf" value="CPF (Apenas números)" class="!font-black text-[10px] uppercase tracking-widest text-[var(--text-heading)]" />
                                <TextInput 
                                    id="cpf" v-model="form.cpf" @input="handleCpfInput" type="text" 
                                    class="block w-full !bg-slate-50 dark:!bg-slate-900/50 !py-4 rounded-xl font-mono text-sm tracking-widest" 
                                    placeholder="000.000.000-00"
                                    required 
                                />
                                <InputError class="mt-2" :message="form.errors.cpf" />
                            </div>

                            <div class="space-y-2">
                                <InputLabel for="telefone" value="Telefone / WhatsApp" class="!font-black text-[10px] uppercase tracking-widest text-[var(--text-heading)]" />
                                <TextInput 
                                    id="telefone" v-model="form.telefones[0].numero" type="text" 
                                    v-mask="'phone'"
                                    class="block w-full !bg-slate-50 dark:!bg-slate-900/50 !py-4 rounded-xl font-mono text-sm tracking-widest" 
                                    placeholder="(00) 00000-0000"
                                    required 
                                />
                                <InputError class="mt-2" :message="form.errors['telefones.0.numero']" />
                            </div>

                            <div class="md:col-span-2 space-y-2">
                                <InputLabel for="email" value="E-mail Pessoal" class="!font-black text-[10px] uppercase tracking-widest text-[var(--text-heading)]" />
                                <TextInput 
                                    id="email" v-model="form.email" type="email" 
                                    class="block w-full !bg-slate-50 dark:!bg-slate-900/50 !py-4 rounded-xl" 
                                    placeholder="exemplo@email.com"
                                    required 
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div class="md:col-span-2 space-y-2">
                                <InputLabel for="matricula" value="Matrícula (Se houver)" class="!font-black text-[10px] uppercase tracking-widest text-[var(--text-heading)]" />
                                <TextInput 
                                    id="matricula" v-model="form.matricula" type="text" 
                                    class="block w-full !bg-slate-50 dark:!bg-slate-900/50 !py-4 rounded-xl font-mono text-sm tracking-widest" 
                                    placeholder="Opcional"
                                />
                                <InputError class="mt-2" :message="form.errors.matricula" />
                            </div>
                        </div>

                        <div class="bg-primary-dynamic/5 p-6 rounded-2xl border border-primary-dynamic/10 flex items-start gap-4">
                            <div class="mt-1">
                                <div class="w-2 h-2 rounded-full bg-primary-dynamic animate-pulse"></div>
                            </div>
                            <p class="text-xs text-[var(--text-main)] opacity-70 leading-relaxed font-medium">
                                Ao prosseguir, sua solicitação será enviada para análise pelo departamento de relacionamento da cooperativa. Caso aprovada, as instruções de acesso ao portal serão encaminhadas para o e-mail cadastrado.
                            </p>
                        </div>

                        <div class="pt-8">
                            <PrimaryButton 
                                :disabled="form.processing"
                                class="w-full justify-center !py-5 shadow-2xl shadow-primary-dynamic/20 text-base"
                            >
                                <span class="font-black uppercase tracking-widest flex items-center justify-center gap-2">
                                    {{ form.processing ? 'Processando envio...' : 'Enviar Solicitação' }}
                                    <ArrowRightIcon v-if="!form.processing" class="w-5 h-5 ml-2 border-2 border-white/20 rounded-full p-0.5" />
                                </span>
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
.text-primary-dynamic {
    color: v-bind(primaryColor);
}
.bg-primary-dynamic {
    background-color: v-bind(primaryColor) !important;
}
.border-primary-dynamic {
    border-color: v-bind(primaryColor) !important;
}
.focus\:ring-primary-dynamic:focus {
    --tw-ring-color: v-bind(primaryColor);
}
.focus\:border-primary-dynamic:focus {
    border-color: v-bind(primaryColor);
}
</style>
