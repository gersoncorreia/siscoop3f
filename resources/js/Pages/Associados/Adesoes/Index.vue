<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { ref, computed } from 'vue';
import { 
    CheckIcon, 
    XMarkIcon, 
    EyeIcon, 
    UserIcon,
    ClockIcon,
    BanknotesIcon,
    CreditCardIcon,
    QrCodeIcon,
    PaperClipIcon,
    DocumentCheckIcon,
    ShieldCheckIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    pendentes: Object,
    aprovadas: Object,
    paymentConfig: Object,
});

const activeTab = ref('pendentes');
const selectedItem = ref(null);
const showAprovarModal = ref(false);
const comprovanteFileName = ref('');

const selectedAuditItem = ref(null);
const showAuditoriaModal = ref(false);

const aprovarForm = useForm({
    forma_pagamento: 'dinheiro',
    observacoes_pagamento: '',
    parcelas: 1,
    comprovante_pix: null,
});

const handleComprovanteUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        aprovarForm.comprovante_pix = file;
        comprovanteFileName.value = file.name;
    }
};

function openAprovarModal(item) {
    selectedItem.value = item;
    aprovarForm.forma_pagamento = 'dinheiro';
    aprovarForm.observacoes_pagamento = '';
    aprovarForm.parcelas = 1;
    aprovarForm.comprovante_pix = null;
    comprovanteFileName.value = '';
    showAprovarModal.value = true;
}

function openAuditoriaModal(item) {
    selectedAuditItem.value = item;
    showAuditoriaModal.value = true;
}

function getFormaPagamentoLabel(forma, parcelas = 1) {
    const map = {
        'dinheiro': '💵 Dinheiro em Espécie',
        'cartao_debito': '💳 Cartão de Débito',
        'cartao_credito': '💳 Cartão de Crédito',
        'pix_manual': '📲 PIX Manual',
        'transferencia': '🏦 Transferência Bancária / TED'
    };
    let label = map[forma] || forma || 'Não informada';
    if (parcelas > 1) {
        label += ` (${parcelas}x)`;
    }
    return label;
}

function submitAprovar() {
    if (!selectedItem.value) return;
    aprovarForm.post(route('associados.adesoes.aprovar', selectedItem.value.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            showAprovarModal.value = false;
            selectedItem.value = null;
        }
    });
}

function rejeitar(id) {
    if (confirm('Tem certeza que deseja recusar esta solicitação de adesão?')) {
        router.post(route('associados.adesoes.rejeitar', id));
    }
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('pt-BR');
}

const resumoAprovar = computed(() => {
    if (!selectedItem.value) return null;

    const taxasAdesao = props.paymentConfig?.taxas_adesao || [
        { id: '1', titulo: 'Taxa Administrativa (Titular)', valor: 100, ativo: true },
        { id: '2', titulo: 'Taxa de Uso do Sistema', valor: 10, ativo: true }
    ];

    const taxaDepLegal = parseFloat(props.paymentConfig?.taxa_dependente_legal ?? 80);
    const taxaDepNaoLegal = parseFloat(props.paymentConfig?.taxa_dependente_nao_legal ?? 100);
    const aporteCotaInicial = parseFloat(props.paymentConfig?.cota_capital_config?.aporte_inicial ?? 10);

    let totalFixos = 0;
    const itensAtivos = [];

    taxasAdesao.forEach(taxa => {
        if (taxa.ativo) {
            const val = parseFloat(taxa.valor) || 0;
            totalFixos += val;
            itensAtivos.push({ titulo: taxa.titulo, valor: val });
        }
    });

    const dependentes = selectedItem.value.dependentes || [];
    let qtdLegais = 0;
    let qtdNaoLegais = 0;

    dependentes.forEach(dep => {
        if (dep.tipo_dependencia === 'nao_legal') {
            qtdNaoLegais++;
        } else {
            qtdLegais++;
        }
    });

    const totalDepLegal = qtdLegais * taxaDepLegal;
    const totalDepNaoLegal = qtdNaoLegais * taxaDepNaoLegal;

    const isCooperativa = usePage().props.tenant?.tipo !== 'fundacao';
    const cotaVal = isCooperativa ? aporteCotaInicial : 0;

    const total = totalFixos + totalDepLegal + totalDepNaoLegal + cotaVal;

    return {
        itensAtivos,
        qtdLegais,
        taxaDepLegal,
        totalDepLegal,
        qtdNaoLegais,
        taxaDepNaoLegal,
        totalDepNaoLegal,
        isCooperativa,
        cotaVal,
        total,
        totalDependentes: dependentes.length,
        autorizaDesconto: selectedItem.value.autoriza_desconto_folha
    };
});
</script>

<template>
    <Head title="Solicitações de Adesão" />

    <AuthenticatedLayout>
        <template #header>
            <Breadcrumbs :items="[{ label: 'Associados', href: '/associados' }, { label: 'Adesões' }]" />
            <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">Solicitações de Adesão</h2>
            <p class="text-sm text-[var(--text-muted)] mt-0.5">Analise, aprove e consulte o histórico de auditoria das adesões.</p>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Tabs Navigation Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-[var(--border-color)] pb-4">
                <div class="flex items-center gap-3">
                    <button 
                        @click="activeTab = 'pendentes'"
                        :class="[
                            'px-5 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2',
                            activeTab === 'pendentes' 
                                ? 'bg-primary-dynamic text-white shadow-lg shadow-primary-dynamic/20' 
                                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'
                        ]"
                    >
                        <ClockIcon class="w-4 h-4" />
                        Aguardando Análise
                        <span class="ml-1 px-2 py-0.5 rounded-full text-[10px] bg-white/20 text-white font-bold">{{ pendentes?.total || 0 }}</span>
                    </button>

                    <button 
                        @click="activeTab = 'aprovadas'"
                        :class="[
                            'px-5 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2',
                            activeTab === 'aprovadas' 
                                ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' 
                                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'
                        ]"
                    >
                        <ShieldCheckIcon class="w-4 h-4" />
                        Histórico de Aprovadas
                        <span class="ml-1 px-2 py-0.5 rounded-full text-[10px] bg-white/20 text-white font-bold">{{ aprovadas?.total || 0 }}</span>
                    </button>
                </div>
            </div>

            <!-- TAB 1: Adesões Pendentes -->
            <div v-if="activeTab === 'pendentes'" class="zynix-table-wrapper">
                <div v-if="pendentes.data.length === 0" class="text-center py-20">
                    <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6">
                        <ClockIcon class="w-10 h-10 text-slate-300" />
                    </div>
                    <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">Nenhuma solicitação pendente</h3>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-2 max-w-xs mx-auto leading-relaxed">
                        Novas adesões aparecerão aqui conforme forem enviadas pelos clientes por meio do link público.
                    </p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="zynix-table w-full">
                        <thead>
                            <tr>
                                <th class="pl-8">Associado</th>
                                <th class="text-center">Documento</th>
                                <th class="text-center">Solicitado em</th>
                                <th class="text-right pr-8">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in pendentes.data" :key="item.id">
                                <td class="pl-8">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-9 h-9 p-2 rounded-xl bg-primary-dynamic/10 text-primary-dynamic shadow-inner">
                                            <UserIcon class="w-full h-full" />
                                        </div>
                                        <div class="ml-4">
                                            <div class="font-black text-[var(--text-heading)] tracking-tight">{{ item.nome }}</div>
                                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-tight">{{ item.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="inline-flex items-center gap-2 font-mono text-xs text-slate-600 dark:text-slate-400">
                                        {{ item.cpf }}
                                    </div>
                                </td>
                                <td class="text-center font-medium">
                                    {{ formatDate(item.created_at) }}
                                </td>
                                <td class="text-right pr-8 whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-1">
                                        <button 
                                            @click="openAprovarModal(item)" 
                                            class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5 shadow-md shadow-emerald-500/20 transition-all"
                                            title="Aprovar e Dar Baixa"
                                        >
                                            <CheckIcon class="w-4 h-4" />
                                            Aprovar & Dar Baixa
                                        </button>
                                        <button 
                                            @click="rejeitar(item.id)" 
                                            class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-all"
                                            title="Rejeitar"
                                        >
                                            <XMarkIcon class="w-4 h-4" />
                                        </button>
                                        <Link 
                                            :href="route('associados.edit', item.id)"
                                            class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-xl transition-all"
                                            title="Ver Detalhes"
                                        >
                                            <EyeIcon class="w-4 h-4" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="pendentes.data.length > 0 && pendentes.links" class="p-6 border-t border-[var(--border-color)] flex items-center justify-between">
                     <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                         Mostrando {{ pendentes.from }}-{{ pendentes.to }} de {{ pendentes.total }}
                     </div>
                </div>
            </div>

            <!-- TAB 2: Histórico de Adesões Aprovadas (Auditoria) -->
            <div v-if="activeTab === 'aprovadas'" class="zynix-table-wrapper">
                <div v-if="!aprovadas || aprovadas.data.length === 0" class="text-center py-20">
                    <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6">
                        <ShieldCheckIcon class="w-10 h-10 text-slate-300" />
                    </div>
                    <h3 class="text-lg font-black text-[var(--text-heading)] tracking-tight">Nenhuma adesão aprovada registrada</h3>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-2 max-w-xs mx-auto leading-relaxed">
                        As aprovações realizadas pelo painel aparecerão neste histórico com os dados de auditoria completos.
                    </p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="zynix-table w-full">
                        <thead>
                            <tr>
                                <th class="pl-8">Associado</th>
                                <th class="text-center">Autorizado Por (Usuário)</th>
                                <th class="text-center">Aprovado Em</th>
                                <th class="text-center">Forma de Pagamento</th>
                                <th class="text-right pr-8">Ações Auditoria</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in aprovadas.data" :key="item.id">
                                <td class="pl-8">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-9 h-9 p-2 rounded-xl bg-emerald-500/10 text-emerald-600 shadow-inner">
                                            <UserIcon class="w-full h-full" />
                                        </div>
                                        <div class="ml-4">
                                            <div class="font-black text-[var(--text-heading)] tracking-tight">{{ item.nome }}</div>
                                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-tight">CPF: {{ item.cpf }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center font-bold text-[var(--text-heading)] text-xs">
                                    {{ item.aprovado_por_nome }}
                                </td>
                                <td class="text-center font-mono text-xs text-slate-600 dark:text-slate-400">
                                    {{ item.aprovado_em || 'N/A' }}
                                </td>
                                <td class="text-center">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-3 py-1 rounded-full">
                                        {{ getFormaPagamentoLabel(item.forma_pagamento_aprovacao, item.parcelas_aprovacao) }}
                                    </span>
                                </td>
                                <td class="text-right pr-8 whitespace-nowrap">
                                    <button 
                                        @click="openAuditoriaModal(item)" 
                                        class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5 shadow-md shadow-indigo-500/20 transition-all"
                                        title="Ver Detalhes da Auditoria"
                                    >
                                        <ShieldCheckIcon class="w-4 h-4" />
                                        Ver Auditoria
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Aprovadas -->
                <div v-if="aprovadas && aprovadas.data.length > 0 && aprovadas.links" class="p-6 border-t border-[var(--border-color)] flex items-center justify-between">
                     <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                         Mostrando {{ aprovadas.from }}-{{ aprovadas.to }} de {{ aprovadas.total }}
                     </div>
                </div>
            </div>

            <!-- Modal Aprovação & Baixa Manual -->
            <Modal :show="showAprovarModal" @close="showAprovarModal = false" max-width="lg">
                <div class="p-6 sm:p-8">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <p class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em] mb-1">Aprovação de Adesão</p>
                            <h2 class="text-xl font-black text-[var(--text-heading)] tracking-tighter">Aprovar & Confirmar Pagamento</h2>
                        </div>
                        <button @click="showAprovarModal = false" class="p-2 text-slate-400 hover:text-slate-600 transition-colors">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Proponente Info Card -->
                    <div v-if="selectedItem" class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl mb-5 border border-[var(--border-color)] space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-[var(--text-heading)]">{{ selectedItem.nome }}</span>
                            <span v-if="resumoAprovar?.totalDependentes > 0" class="text-[10px] font-bold bg-primary-dynamic/10 text-primary-dynamic px-2 py-0.5 rounded-full">
                                {{ resumoAprovar.totalDependentes }} dependente(s)
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 font-mono">CPF: {{ selectedItem.cpf }} | {{ selectedItem.email }}</p>
                    </div>

                    <form @submit.prevent="submitAprovar" class="space-y-5">
                        <!-- Select Forma de Pagamento -->
                        <div>
                            <InputLabel value="Forma de Pagamento Recebida *" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                            <select v-model="aprovarForm.forma_pagamento" class="mt-1.5 w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] py-2.5 px-3 text-xs font-bold">
                                <option value="dinheiro">💵 Dinheiro em Espécie (Recepção)</option>
                                <option value="cartao_debito">💳 Cartão de Débito (Maquininha)</option>
                                <option value="cartao_credito">💳 Cartão de Crédito (Maquininha)</option>
                                <option value="pix_manual">📲 PIX Manual (Chave/QRCode da Entidade)</option>
                                <option value="transferencia">🏦 Transferência Bancária / TED</option>
                            </select>
                        </div>

                        <!-- Condicional 1: Upload Comprovante PIX -->
                        <div v-if="aprovarForm.forma_pagamento === 'pix_manual'" class="p-4 bg-emerald-500/5 border border-emerald-500/20 rounded-2xl space-y-2">
                            <InputLabel value="Anexar Comprovante PIX (Opcional)" class="!text-[10px] !font-black !uppercase !tracking-widest text-emerald-600 dark:text-emerald-400" />
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                                <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-sm hover:bg-emerald-600 transition-colors">
                                    <PaperClipIcon class="w-4 h-4" />
                                    Selecionar Comprovante
                                    <input type="file" @change="handleComprovanteUpload" accept="image/*,application/pdf" class="hidden" />
                                </label>
                                <span v-if="comprovanteFileName" class="text-xs font-medium text-emerald-700 dark:text-emerald-300 truncate max-w-[200px]">
                                    {{ comprovanteFileName }}
                                </span>
                                <span v-else class="text-xs text-slate-400">Nenhum arquivo anexado</span>
                            </div>
                            <InputError :message="aprovarForm.errors.comprovante_pix" />
                        </div>

                        <!-- Condicional 2: Parcelamento no Cartão -->
                        <div v-if="aprovarForm.forma_pagamento === 'cartao_credito' || aprovarForm.forma_pagamento === 'cartao_debito'" class="p-4 bg-indigo-500/5 border border-indigo-500/20 rounded-2xl space-y-2">
                            <InputLabel value="Condição de Parcelamento *" class="!text-[10px] !font-black !uppercase !tracking-widest text-indigo-600 dark:text-indigo-400" />
                            <select v-model="aprovarForm.parcelas" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] text-xs py-2.5 px-3 font-bold">
                                <option :value="1">1x À Vista (Sem parcelamento)</option>
                                <option v-for="n in 11" :key="n + 1" :value="n + 1">{{ n + 1 }}x Parcelado</option>
                            </select>
                        </div>

                        <!-- Observações / Código da Transação -->
                        <div>
                            <InputLabel value="Observações / Código da Transação / NSU (Opcional)" class="!text-[10px] !font-black !uppercase !tracking-widest" />
                            <input 
                                type="text" 
                                v-model="aprovarForm.observacoes_pagamento" 
                                class="mt-1.5 w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] text-xs py-2.5 px-3" 
                                placeholder="Ex: Autorização maquininha #98231, Código E2E PIX..."
                            />
                        </div>

                        <!-- Resumo Visual do que está sendo Aprovado (Após Observações) -->
                        <div v-if="resumoAprovar" class="pt-4 border-t border-[var(--border-color)]">
                            <div class="bg-primary-dynamic/5 rounded-2xl p-4 border border-primary-dynamic/15 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-primary-dynamic flex items-center gap-1.5">
                                        <DocumentCheckIcon class="w-4 h-4" />
                                        Resumo da Aprovação & Baixa
                                    </span>
                                    <span v-if="resumoAprovar.isCooperativa" class="text-[9px] font-bold text-slate-400 uppercase">
                                        Desconto em Folha: {{ resumoAprovar.autorizaDesconto ? 'Sim' : 'Não' }}
                                    </span>
                                </div>

                                <div class="space-y-1.5 text-xs text-[var(--text-muted)] border-t border-primary-dynamic/10 pt-3">
                                    <div v-for="(item, idx) in resumoAprovar.itensAtivos" :key="idx" class="flex justify-between items-center">
                                        <span>{{ item.titulo }}</span>
                                        <span class="font-bold text-[var(--text-heading)]">R$ {{ item.valor.toFixed(2).replace('.', ',') }}</span>
                                    </div>
                                    <div v-if="resumoAprovar.qtdLegais > 0" class="flex justify-between items-center">
                                        <span>Dependentes Legais ({{ resumoAprovar.qtdLegais }}x R$ {{ resumoAprovar.taxaDepLegal.toFixed(2).replace('.', ',') }})</span>
                                        <span class="font-bold text-[var(--text-heading)]">R$ {{ resumoAprovar.totalDepLegal.toFixed(2).replace('.', ',') }}</span>
                                    </div>
                                    <div v-if="resumoAprovar.qtdNaoLegais > 0" class="flex justify-between items-center">
                                        <span>Dependentes Não Legais ({{ resumoAprovar.qtdNaoLegais }}x R$ {{ resumoAprovar.taxaDepNaoLegal.toFixed(2).replace('.', ',') }})</span>
                                        <span class="font-bold text-[var(--text-heading)]">R$ {{ resumoAprovar.totalDepNaoLegal.toFixed(2).replace('.', ',') }}</span>
                                    </div>
                                    <div v-if="resumoAprovar.isCooperativa" class="flex justify-between items-center">
                                        <span>Cota Capital (Aporte Inicial)</span>
                                        <span class="font-bold text-[var(--text-heading)]">R$ {{ resumoAprovar.cotaVal.toFixed(2).replace('.', ',') }}</span>
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-primary-dynamic/15 flex justify-between items-center">
                                    <span class="text-xs font-black uppercase tracking-widest text-[var(--text-heading)]">Total a Baixar Hoje:</span>
                                    <span class="text-lg font-black text-emerald-600 dark:text-emerald-400 font-mono">R$ {{ resumoAprovar.total.toFixed(2).replace('.', ',') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Buttons -->
                        <div class="pt-4 flex justify-end gap-3 border-t border-[var(--border-color)]">
                            <SecondaryButton @click="showAprovarModal = false" type="button" class="!rounded-full !py-3 !px-6">Cancelar</SecondaryButton>
                            <PrimaryButton type="submit" :disabled="aprovarForm.processing" class="!rounded-full !py-3 !px-6 !bg-emerald-500 hover:!bg-emerald-600 shadow-lg shadow-emerald-500/20">
                                {{ aprovarForm.processing ? 'Aprovando...' : 'Aprovar & Confirmar' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>

            <!-- Modal Auditoria de Aprovação -->
            <Modal :show="showAuditoriaModal" @close="showAuditoriaModal = false" max-width="lg">
                <div v-if="selectedAuditItem" class="p-6 sm:p-8 space-y-6">
                    <div class="flex items-center justify-between border-b border-[var(--border-color)] pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-bold shadow-inner">
                                <ShieldCheckIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em]">Auditoria de Aprovação</p>
                                <h2 class="text-xl font-black text-[var(--text-heading)] tracking-tighter">Detalhes da Adesão Autorizada</h2>
                            </div>
                        </div>
                        <button @click="showAuditoriaModal = false" class="p-2 text-slate-400 hover:text-slate-600 transition-colors">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Card do Proponente -->
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl border border-[var(--border-color)] space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-black text-[var(--text-heading)]">{{ selectedAuditItem.nome }}</span>
                            <span class="text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                                Status: {{ selectedAuditItem.status }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 font-mono">CPF: {{ selectedAuditItem.cpf }} | {{ selectedAuditItem.email }}</p>
                    </div>

                    <!-- Grid de Detalhes da Autorização -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Quem Autorizou -->
                        <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-[var(--border-color)] space-y-1">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Usuário Autorizador</span>
                            <p class="text-xs font-black text-[var(--text-heading)] flex items-center gap-1.5 mt-1">
                                <UserIcon class="w-4 h-4 text-primary-dynamic" />
                                {{ selectedAuditItem.aprovado_por_nome }}
                            </p>
                            <p class="text-[10px] text-slate-400 font-mono mt-1">Aprovado em: {{ selectedAuditItem.aprovado_em || 'N/A' }}</p>
                        </div>

                        <!-- Pagamento & Parcelas -->
                        <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-[var(--border-color)] space-y-1">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Forma de Pagamento</span>
                            <p class="text-xs font-black text-[var(--text-heading)] mt-1">
                                {{ getFormaPagamentoLabel(selectedAuditItem.forma_pagamento_aprovacao, selectedAuditItem.parcelas_aprovacao) }}
                            </p>
                            <p class="text-[10px] text-slate-400 mt-1">
                                Data da Adesão: {{ selectedAuditItem.data_adesao }}
                            </p>
                        </div>
                    </div>

                    <!-- Comprovante Anexo (se houver) -->
                    <div v-if="selectedAuditItem.comprovante_pix_url" class="p-4 bg-emerald-500/5 rounded-2xl border border-emerald-500/20 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <PaperClipIcon class="w-5 h-5 text-emerald-600" />
                            <span class="text-xs font-bold text-[var(--text-heading)]">Comprovante PIX Anexado</span>
                        </div>
                        <a :href="selectedAuditItem.comprovante_pix_url" target="_blank" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold transition-colors inline-flex items-center gap-1.5 shadow-sm">
                            <EyeIcon class="w-4 h-4" />
                            Visualizar Comprovante
                        </a>
                    </div>

                    <!-- Observações / NSU -->
                    <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-[var(--border-color)] space-y-1">
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Observações / Código da Transação (NSU)</span>
                        <p class="text-xs text-[var(--text-heading)] font-mono leading-relaxed mt-1">
                            {{ selectedAuditItem.observacoes_aprovacao || 'Nenhuma observação informada.' }}
                        </p>
                    </div>

                    <!-- Resumo Financeiro Baixado na Época da Aprovação -->
                    <div v-if="selectedAuditItem.detalhes_financeiro_aprovacao" class="p-4 bg-primary-dynamic/5 rounded-2xl border border-primary-dynamic/15 space-y-3">
                        <span class="text-[10px] font-black uppercase tracking-widest text-primary-dynamic flex items-center gap-1.5">
                            <DocumentCheckIcon class="w-4 h-4" />
                            Lançamentos Financeiros Registrados na Aprovação
                        </span>

                        <div class="space-y-1.5 text-xs text-[var(--text-muted)] border-t border-primary-dynamic/10 pt-3">
                            <div v-for="(item, idx) in selectedAuditItem.detalhes_financeiro_aprovacao.itensAtivos" :key="idx" class="flex justify-between items-center">
                                <span>{{ item.titulo }}</span>
                                <span class="font-bold text-[var(--text-heading)]">R$ {{ Number(item.valor).toFixed(2).replace('.', ',') }}</span>
                            </div>
                            <div v-if="selectedAuditItem.detalhes_financeiro_aprovacao.qtdLegais > 0" class="flex justify-between items-center">
                                <span>Dependentes Legais ({{ selectedAuditItem.detalhes_financeiro_aprovacao.qtdLegais }}x R$ {{ Number(selectedAuditItem.detalhes_financeiro_aprovacao.taxaDepLegal).toFixed(2).replace('.', ',') }})</span>
                                <span class="font-bold text-[var(--text-heading)]">R$ {{ Number(selectedAuditItem.detalhes_financeiro_aprovacao.totalDepLegal).toFixed(2).replace('.', ',') }}</span>
                            </div>
                            <div v-if="selectedAuditItem.detalhes_financeiro_aprovacao.qtdNaoLegais > 0" class="flex justify-between items-center">
                                <span>Dependentes Não Legais ({{ selectedAuditItem.detalhes_financeiro_aprovacao.qtdNaoLegais }}x R$ {{ Number(selectedAuditItem.detalhes_financeiro_aprovacao.taxaDepNaoLegal).toFixed(2).replace('.', ',') }})</span>
                                <span class="font-bold text-[var(--text-heading)]">R$ {{ Number(selectedAuditItem.detalhes_financeiro_aprovacao.totalDepNaoLegal).toFixed(2).replace('.', ',') }}</span>
                            </div>
                            <div v-if="selectedAuditItem.detalhes_financeiro_aprovacao.isCooperativa" class="flex justify-between items-center">
                                <span>Cota Capital (Aporte Inicial)</span>
                                <span class="font-bold text-[var(--text-heading)]">R$ {{ Number(selectedAuditItem.detalhes_financeiro_aprovacao.cotaVal).toFixed(2).replace('.', ',') }}</span>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-primary-dynamic/15 flex justify-between items-center">
                            <span class="text-xs font-black uppercase tracking-widest text-[var(--text-heading)]">Total Baixado:</span>
                            <span class="text-lg font-black text-emerald-600 dark:text-emerald-400 font-mono">R$ {{ Number(selectedAuditItem.detalhes_financeiro_aprovacao.totalGeral).toFixed(2).replace('.', ',') }}</span>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end border-t border-[var(--border-color)]">
                        <SecondaryButton @click="showAuditoriaModal = false" type="button" class="!rounded-full !py-2.5 !px-6">
                            Fechar Auditoria
                        </SecondaryButton>
                    </div>
                </div>
            </Modal>
        </div>
    </AuthenticatedLayout>
</template>
