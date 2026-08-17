<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { 
    UserIcon, 
    UserGroupIcon, 
    ArrowRightIcon, 
    CheckCircleIcon,
    ShieldExclamationIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    tenant: Object
});

const primaryColor = computed(() => props.tenant?.primary_color || '#2563eb');
const showExistingModal = ref(false);
const duplicateFields = ref([]);

const form = useForm({
    nome: '',
    cpf: '',
    email: '',
    matricula: '',
    telefones: [
        { numero: '', tipo: 'celular' }
    ],
    dependentes: [],
    forma_pagamento_adesao: '',
    autoriza_desconto_folha: false,
});

const addDependente = () => {
    form.dependentes.push({ nome: '', cpf: '', parentesco: '', tipo_dependencia: 'legal', autoriza_acesso: false, email: '' });
};

const removeDependente = (index) => {
    form.dependentes.splice(index, 1);
};

const resumoFinanceiro = computed(() => {
    const taxaTitular = 100;
    const taxaUsoSistema = 10;
    const primeiraCota = 10;

    let totalDependentesLegais = 0;
    let totalDependentesNaoLegais = 0;
    let qtdLegais = 0;
    let qtdNaoLegais = 0;

    form.dependentes.forEach(dep => {
        if (dep.tipo_dependencia === 'nao_legal') {
            totalDependentesNaoLegais += 100;
            qtdNaoLegais++;
        } else {
            totalDependentesLegais += 80;
            qtdLegais++;
        }
    });

    const totalDependentes = totalDependentesLegais + totalDependentesNaoLegais;
    const total = taxaTitular + taxaUsoSistema + totalDependentes + primeiraCota;
    
    return {
        titular: taxaTitular,
        taxaUsoSistema,
        qtdLegais,
        qtdNaoLegais,
        totalDependentesLegais,
        totalDependentesNaoLegais,
        totalDependentes,
        cota: primeiraCota,
        total
    };
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

function maskName(value) {
    if (!value) return '';
    const parts = value.trim().split(/\s+/);
    return parts.map(part => {
        if (part.length <= 2) return part;
        return part.substring(0, 3) + '*'.repeat(Math.min(part.length - 3, 5));
    }).join(' ');
}

function maskCPF(value) {
    if (!value) return '';
    const digits = value.replace(/\D/g, '');
    if (digits.length < 11) return value;
    return digits.substring(0, 3) + '.***.***-' + digits.substring(9, 11);
}

function maskEmail(value) {
    if (!value || !value.includes('@')) return value;
    const [user, domain] = value.split('@');
    if (user.length <= 3) {
        return user[0] + '***@' + domain;
    }
    const visible = user.substring(0, 3);
    const hidden = '*'.repeat(Math.min(user.length - 3, 8));
    return `${visible}${hidden}@${domain}`;
}

function submitForm() {
    form.post(route('adesao.store'), {
        onError: (errors) => {
            const fields = [];
            if (errors.nome && errors.nome.includes('Identificamos')) {
                fields.push(`Nome (${maskName(form.nome)})`);
            }
            if (errors.cpf && errors.cpf.includes('Identificamos')) {
                fields.push(`CPF (${maskCPF(form.cpf)})`);
            }
            if (errors.email && errors.email.includes('Identificamos')) {
                fields.push(`E-mail (${maskEmail(form.email)})`);
            }

            if (fields.length > 0) {
                duplicateFields.value = fields;
                showExistingModal.value = true;
            }
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

                        <!-- Dependentes Section -->
                        <div class="pt-6 border-t border-[var(--border-color)]/30">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-xl font-black text-[var(--text-heading)] tracking-tighter">Dependentes</h3>
                                    <p class="text-[11px] uppercase tracking-widest font-bold text-slate-400 mt-1">Adicione familiares (Opcional)</p>
                                </div>
                                <button type="button" @click="addDependente" class="text-xs font-black uppercase tracking-widest text-primary-dynamic hover:text-primary-dynamic/80 bg-primary-dynamic/10 px-4 py-2 rounded-full transition-all">
                                    + Adicionar Dependente
                                </button>
                            </div>

                            <div v-if="form.dependentes.length === 0" class="text-center py-6 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                                <p class="text-xs text-slate-400 font-medium">Nenhum dependente adicionado.</p>
                            </div>

                            <div v-else class="space-y-4">
                                <div v-for="(dep, index) in form.dependentes" :key="index" class="p-5 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-100 dark:border-slate-800 relative">
                                    <button type="button" @click="removeDependente(index)" class="absolute top-4 right-4 text-rose-400 hover:text-rose-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="space-y-1">
                                            <InputLabel value="Nome do Dependente" class="!font-black text-[9px] uppercase tracking-widest" />
                                            <TextInput v-model="dep.nome" type="text" class="block w-full !py-2.5 text-sm rounded-lg" required />
                                        </div>
                                        <div class="space-y-1">
                                            <InputLabel value="CPF" class="!font-black text-[9px] uppercase tracking-widest" />
                                            <TextInput v-model="dep.cpf" type="text" v-mask="'###.###.###-##'" class="block w-full !py-2.5 text-sm rounded-lg font-mono tracking-wider" />
                                        </div>
                                        <div class="space-y-1">
                                            <InputLabel value="Parentesco" class="!font-black text-[9px] uppercase tracking-widest" />
                                            <select v-model="dep.parentesco" required class="block w-full !py-2.5 text-sm rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-slate-200 focus:border-primary-dynamic focus:ring focus:ring-primary-dynamic/20">
                                                <option value="">Selecione</option>
                                                <option value="Cônjuge">Cônjuge</option>
                                                <option value="Filho(a)">Filho(a)</option>
                                                <option value="Pai/Mãe">Pai/Mãe</option>
                                                <option value="Outro">Outro</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Tipo de Dependência -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                                        <div class="space-y-1">
                                            <InputLabel value="Tipo de Dependência" class="!font-black text-[9px] uppercase tracking-widest" />
                                            <select v-model="dep.tipo_dependencia" class="block w-full !py-2.5 text-sm rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-slate-200 focus:border-primary-dynamic focus:ring focus:ring-primary-dynamic/20">
                                                <option value="legal">Dependente Legal (R$ 80,00)</option>
                                                <option value="nao_legal">Dependente Não Legal (R$ 100,00)</option>
                                            </select>
                                            <p class="text-[10px] text-slate-400 mt-1">Dependentes não legais possuem taxa equivalente à do titular.</p>
                                        </div>

                                        <!-- Autorizar Acesso ao Portal -->
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-3 mt-5">
                                                <input 
                                                    type="checkbox" 
                                                    :id="'acesso_dep_' + index" 
                                                    v-model="dep.autoriza_acesso" 
                                                    class="w-5 h-5 rounded border-slate-300 dark:border-slate-600 text-primary-dynamic focus:ring-primary-dynamic/30"
                                                />
                                                <label :for="'acesso_dep_' + index" class="text-sm font-bold text-[var(--text-heading)] cursor-pointer">
                                                    Autorizar acesso ao portal
                                                </label>
                                            </div>
                                            <p class="text-[10px] text-slate-400 pl-8">O dependente receberá login e senha próprios por e-mail.</p>
                                        </div>
                                    </div>

                                    <!-- Email do Dependente (se autorizar acesso) -->
                                    <div v-if="dep.autoriza_acesso" class="mt-4 transition-all">
                                        <div class="space-y-1">
                                            <InputLabel :value="'E-mail do Dependente'" class="!font-black text-[9px] uppercase tracking-widest" />
                                            <TextInput v-model="dep.email" type="email" class="block w-full !py-2.5 text-sm rounded-lg" placeholder="email.dependente@exemplo.com" required />
                                            <InputError :message="form.errors['dependentes.' + index + '.email']" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resumo Financeiro -->
                        <div class="pt-6 border-t border-[var(--border-color)]/30">
                            <h3 class="text-xl font-black text-[var(--text-heading)] tracking-tighter mb-4">Resumo Financeiro</h3>
                            
                            <div class="bg-primary-dynamic/5 rounded-2xl border border-primary-dynamic/10 overflow-hidden">
                                <div class="p-6 space-y-3">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-[var(--text-muted)] font-medium">Taxa Administrativa (Titular)</span>
                                        <span class="font-bold text-[var(--text-heading)]">R$ {{ resumoFinanceiro.titular.toFixed(2).replace('.', ',') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-[var(--text-muted)] font-medium">Taxa de Uso do Sistema</span>
                                        <span class="font-bold text-[var(--text-heading)]">R$ {{ resumoFinanceiro.taxaUsoSistema.toFixed(2).replace('.', ',') }}</span>
                                    </div>
                                    <div v-if="resumoFinanceiro.qtdLegais > 0" class="flex justify-between items-center text-sm">
                                        <span class="text-[var(--text-muted)] font-medium">Dependentes Legais ({{ resumoFinanceiro.qtdLegais }}x R$ 80,00)</span>
                                        <span class="font-bold text-[var(--text-heading)]">R$ {{ resumoFinanceiro.totalDependentesLegais.toFixed(2).replace('.', ',') }}</span>
                                    </div>
                                    <div v-if="resumoFinanceiro.qtdNaoLegais > 0" class="flex justify-between items-center text-sm">
                                        <span class="text-[var(--text-muted)] font-medium">Dependentes Não Legais ({{ resumoFinanceiro.qtdNaoLegais }}x R$ 100,00)</span>
                                        <span class="font-bold text-[var(--text-heading)]">R$ {{ resumoFinanceiro.totalDependentesNaoLegais.toFixed(2).replace('.', ',') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-[var(--text-muted)] font-medium">Cota Capital (Aporte Inicial)</span>
                                        <span class="font-bold text-[var(--text-heading)]">R$ {{ resumoFinanceiro.cota.toFixed(2).replace('.', ',') }}</span>
                                    </div>
                                </div>
                                <div class="bg-primary-dynamic/10 p-5 flex justify-between items-center">
                                    <span class="text-xs font-black uppercase tracking-widest text-primary-dynamic">Total a Pagar Hoje</span>
                                    <span class="text-2xl font-black text-primary-dynamic">R$ {{ resumoFinanceiro.total.toFixed(2).replace('.', ',') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Autorização de Desconto em Folha (Apenas para Cooperativas, omitido para Fundação) -->
                        <div v-if="props.tenant?.tipo !== 'fundacao'" class="pt-6 border-t border-[var(--border-color)]/30">
                            <div class="bg-amber-500/5 dark:bg-amber-500/10 p-6 rounded-2xl border border-amber-500/20">
                                <div class="flex items-start gap-4">
                                    <input 
                                        type="checkbox" 
                                        id="autoriza_desconto" 
                                        v-model="form.autoriza_desconto_folha" 
                                        class="w-6 h-6 mt-1 rounded border-amber-300 dark:border-amber-600 text-amber-500 focus:ring-amber-500/30 flex-shrink-0"
                                    />
                                    <label for="autoriza_desconto" class="cursor-pointer">
                                        <span class="block text-sm font-black text-[var(--text-heading)]">
                                            Autorizo o desconto em folha de pagamento
                                        </span>
                                        <span class="block text-xs text-[var(--text-muted)] mt-1 leading-relaxed">
                                            Declaro que autorizo a entidade a efetuar os descontos referentes às taxas administrativas e encargos diretamente em minha folha de pagamento. Esta autorização é obrigatória para a conclusão da adesão.
                                        </span>
                                    </label>
                                </div>
                                <InputError class="mt-3" :message="form.errors.autoriza_desconto_folha" />
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

        <!-- Modal Premium de Registro Prévio Identificado -->
        <Modal :show="showExistingModal" @close="showExistingModal = false" maxWidth="md">
            <div class="p-6 text-center space-y-4">
                <!-- Badge Icon de Segurança -->
                <div class="inline-flex items-center justify-center w-16 h-16 bg-amber-500/10 text-amber-500 rounded-3xl shadow-lg shadow-amber-500/10">
                    <ShieldExclamationIcon class="w-9 h-9" />
                </div>

                <div>
                    <h3 class="text-xl font-black text-[var(--text-heading)] tracking-tight">
                        Registro Prévio Identificado
                    </h3>
                    <p class="text-xs text-[var(--text-muted)] mt-1.5 leading-relaxed">
                        Identificamos que os seguintes dados informados já constam em nossos registros de solicitações:
                    </p>
                </div>

                <!-- Badges de Informações Encontradas -->
                <div class="flex flex-wrap items-center justify-center gap-2 py-1">
                    <span v-for="field in duplicateFields" :key="field" class="text-xs font-mono font-bold text-amber-700 bg-amber-50 dark:bg-amber-500/10 px-3 py-1.5 rounded-xl border border-amber-200 dark:border-amber-500/20">
                        {{ field }}
                    </span>
                </div>

                <!-- Mensagem Orientadora -->
                <div class="p-4 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-[var(--border-color)]/50 text-left text-xs text-slate-600 dark:text-slate-300 leading-relaxed space-y-2">
                    <p>
                        Para verificar o andamento da sua proposta, solicitar atualização cadastral ou tirar dúvidas, por favor, entre em contato diretamente com a equipe de atendimento da <strong class="text-[var(--text-heading)]">{{ tenant?.nome || 'Santa Casa da Amazônia' }}</strong>.
                    </p>
                    <p class="text-[11px] text-slate-400">
                        Se a sua adesão já tiver sido aprovada anteriormente, você pode acessar diretamente o Portal com suas credenciais.
                    </p>
                </div>

                <!-- Botões de Ação do Pop-up -->
                <div class="pt-2 space-y-2">
                    <Link 
                        :href="route('login')" 
                        class="w-full py-3 bg-primary-dynamic text-white font-black text-xs uppercase tracking-widest rounded-xl inline-flex items-center justify-center gap-2 shadow-lg shadow-primary-dynamic/20 hover:opacity-95 transition-all"
                    >
                        Fazer Login no Portal
                        <ArrowRightIcon class="w-4 h-4" />
                    </Link>

                    <button 
                        type="button"
                        @click="showExistingModal = false" 
                        class="w-full py-2.5 text-xs font-bold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-all"
                    >
                        Entendido / Fechar
                    </button>
                </div>
            </div>
        </Modal>
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
