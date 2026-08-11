<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { 
    ShieldCheckIcon, 
    ArrowLeftIcon, 
    PlusIcon, 
    TrashIcon, 
    CheckCircleIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    titulo: props.settings.titulo || '',
    introducao: props.settings.introducao || '',
    botao_texto: props.settings.botao_texto || '',
    secoes: props.settings.secoes ? JSON.parse(JSON.stringify(props.settings.secoes)) : [],
});

const addSection = () => {
    form.secoes.push({
        titulo: 'Nova Seção',
        conteudo: 'Conteúdo da nova seção...'
    });
};

const removeSection = (index) => {
    form.secoes.splice(index, 1);
};

const moveUp = (index) => {
    if (index === 0) return;
    const temp = form.secoes[index];
    form.secoes[index] = form.secoes[index - 1];
    form.secoes[index - 1] = temp;
};

const moveDown = (index) => {
    if (index === form.secoes.length - 1) return;
    const temp = form.secoes[index];
    form.secoes[index] = form.secoes[index + 1];
    form.secoes[index + 1] = temp;
};

const resetToDefault = () => {
    if (confirm('Deseja realmente restaurar os termos padrão da LGPD? Suas alterações serão perdidas.')) {
        form.titulo = 'Termos de Uso e Privacidade (LGPD)';
        form.introducao = 'Para continuar acessando o Siscoop3F e garantir sua segurança, solicitamos que você leia e concorde com nossa política de privacidade em adequação à Lei Geral de Proteção de Dados (LGPD).';
        form.botao_texto = 'Eu Li e Concordo com os Termos';
        form.secoes = [
            {
                titulo: '1. Coleta de Dados',
                conteudo: 'O Siscoop3F coleta informações pessoais estritamente necessárias para o gerenciamento das atividades cooperativas/institucionais, como identificação, dados de contato e financeiros das cotas de capital.'
            },
            {
                titulo: '2. Uso e Compartilhamento',
                conteudo: 'Os dados coletados serão usados exclusivamente pela sua entidade para fins administrativos e de prestação de serviços não sendo compartilhados com terceiros sem consentimento explícito, salvo obrigações legais.'
            },
            {
                titulo: '3. Seus Direitos',
                conteudo: 'O associado/cliente possui o direito de exigir a alteração e anonimização de dados caso retire-se da organização.'
            }
        ];
    }
};

const submit = () => {
    form.post(route('configuracoes.lgpd.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Configurar LGPD" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <Breadcrumbs :items="[{ label: 'Ajustes', href: route('configuracoes.index') }, { label: 'LGPD' }]" />
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Personalização de Termos (LGPD)</h2>
                    <p class="text-sm text-[var(--text-muted)] mt-0.5">Edite as políticas e termos exibidos aos associados/clientes.</p>
                </div>
                <Link :href="route('configuracoes.index')">
                    <SecondaryButton class="!rounded-full !py-2.5">
                        <ArrowLeftIcon class="w-4 h-4 mr-2" /> Voltar para Ajustes
                    </SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <div v-if="$page.props.flash?.success" class="p-4 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20 rounded-2xl font-bold flex items-center gap-3">
                <CheckCircleIcon class="w-5 h-5 text-emerald-500" />
                {{ $page.props.flash.success }}
            </div>

                <div class="grid grid-cols-1 gap-8">
                    <!-- Form Card -->
                    <div class="zynix-card overflow-hidden !p-0">
                        <div class="p-8 border-b border-[var(--border-color)] flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-amber-500/10 text-amber-500 rounded-xl shadow-inner border border-amber-500/20">
                                    <ShieldCheckIcon class="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 class="font-black text-[var(--text-heading)] leading-tight tracking-tight text-lg">Termo de Aceite LGPD</h3>
                                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mt-1">Configure o documento que os usuários precisam aceitar</p>
                                </div>
                            </div>
                            <button @click="resetToDefault" type="button" class="text-xs font-bold text-amber-600 hover:text-amber-700 flex items-center gap-1.5 transition-colors">
                                <ArrowPathIcon class="w-4 h-4" /> Restaurar Termo Padrão
                            </button>
                        </div>

                        <form @submit.prevent="submit" class="p-8 space-y-8">
                            <!-- Título e Introdução -->
                            <div class="space-y-6">
                                <div>
                                    <InputLabel for="titulo" value="Título do Documento" class="!font-black tracking-widest uppercase text-[10px] mb-3" />
                                    <TextInput id="titulo" v-model="form.titulo" type="text" class="block w-full" required placeholder="Ex: Termos de Uso e Privacidade (LGPD)" />
                                    <InputError class="mt-2" :message="form.errors.titulo" />
                                </div>

                                <div>
                                    <InputLabel for="introducao" value="Texto de Introdução" class="!font-black tracking-widest uppercase text-[10px] mb-3" />
                                    <textarea 
                                        id="introducao" 
                                        v-model="form.introducao" 
                                        rows="3" 
                                        class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-body)] focus:ring-primary-dynamic focus:border-primary-dynamic py-3 px-4 text-sm font-bold transition-all" 
                                        required 
                                        placeholder="Introdução explicativa exibida aos usuários..."
                                    ></textarea>
                                    <InputError class="mt-2" :message="form.errors.introducao" />
                                </div>
                            </div>

                            <!-- Seções Dinâmicas -->
                            <div class="pt-6 border-t border-[var(--border-color)] space-y-6">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs uppercase font-black tracking-wider text-slate-400">Seções e Cláusulas</h4>
                                    <button @click="addSection" type="button" class="btn-primary !py-2 !px-4 !text-[10px] !font-black !uppercase !tracking-wider !rounded-lg flex items-center gap-1.5">
                                        <PlusIcon class="w-4 h-4" /> Adicionar Seção
                                    </button>
                                </div>

                                <div v-if="form.secoes.length === 0" class="p-8 text-center border-2 border-dashed border-[var(--border-color)] rounded-2xl">
                                    <p class="text-xs font-bold text-slate-400">Nenhuma seção adicionada. Adicione pelo menos uma cláusula explicativa.</p>
                                </div>

                                <div v-else class="space-y-4">
                                    <div 
                                        v-for="(secao, idx) in form.secoes" 
                                        :key="idx" 
                                        class="p-5 border border-[var(--border-color)] rounded-2xl bg-slate-50/50 dark:bg-slate-800/10 space-y-4 relative group"
                                    >
                                        <!-- Actions header -->
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-black text-primary-dynamic uppercase tracking-widest">Cláusula #{{ idx + 1 }}</span>
                                            <div class="flex items-center gap-1.5">
                                                <button @click="moveUp(idx)" type="button" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 disabled:opacity-30" :disabled="idx === 0">
                                                    <ArrowUpIcon class="w-3.5 h-3.5" />
                                                </button>
                                                <button @click="moveDown(idx)" type="button" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 disabled:opacity-30" :disabled="idx === form.secoes.length - 1">
                                                    <ArrowDownIcon class="w-3.5 h-3.5" />
                                                </button>
                                                <button @click="removeSection(idx)" type="button" class="p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-lg transition">
                                                    <TrashIcon class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 gap-4">
                                            <div>
                                                <input 
                                                    v-model="secao.titulo" 
                                                    type="text" 
                                                    class="w-full rounded-lg border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-body)] focus:ring-primary-dynamic focus:border-primary-dynamic py-2 px-3 text-xs font-bold transition-all" 
                                                    required 
                                                    placeholder="Título da Cláusula (Ex: 1. Coleta de Dados)"
                                                />
                                            </div>
                                            <div>
                                                <textarea 
                                                    v-model="secao.conteudo" 
                                                    rows="2" 
                                                    class="w-full rounded-lg border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-body)] focus:ring-primary-dynamic focus:border-primary-dynamic py-2 px-3 text-xs font-semibold transition-all" 
                                                    required 
                                                    placeholder="Detalhamento das regras e privacidade..."
                                                ></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botão de Aceite -->
                            <div class="pt-6 border-t border-[var(--border-color)]">
                                <div>
                                    <InputLabel for="botao_texto" value="Texto do Botão de Aceite" class="!font-black tracking-widest uppercase text-[10px] mb-3" />
                                    <TextInput id="botao_texto" v-model="form.botao_texto" type="text" class="block w-full" required placeholder="Ex: Eu Li e Concordo com os Termos" />
                                    <InputError class="mt-2" :message="form.errors.botao_texto" />
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="pt-6 border-t border-[var(--border-color)]">
                                <PrimaryButton 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="w-full !py-4 !text-[11px] !tracking-[0.25em] !font-black !rounded-full shadow-2xl transition-all"
                                >
                                    <CheckCircleIcon v-if="!form.processing" class="w-5 h-5 mr-3" />
                                    <span v-else class="mr-3 animate-spin border-2 border-white/20 border-t-white rounded-full w-4 h-4"></span>
                                    SALVAR E APLICAR NOVOS TERMOS
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
        </div>
    </AuthenticatedLayout>
</template>
