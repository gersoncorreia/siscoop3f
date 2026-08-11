<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    DocumentArrowDownIcon, 
    BanknotesIcon, 
    UserGroupIcon, 
    ClipboardDocumentListIcon 
} from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';

const filters = ref({
    associados: { data_inicio: '', data_fim: '' },
    financeiro: { data_inicio: '', data_fim: '', status: '' },
    dependentes: { data_inicio: '', data_fim: '' },
    adesoes: { data_inicio: '', data_fim: '' },
    agendamentos: { data_inicio: '', data_fim: '', status: '' },
    prestadores: { data_inicio: '', data_fim: '' },
});

const exportExcel = (type) => {
    let params = new URLSearchParams();
    const filter = filters.value[type];
    
    if (filter && filter.data_inicio) params.append('data_inicio', filter.data_inicio);
    if (filter && filter.data_fim) params.append('data_fim', filter.data_fim);
    if (filter && filter.status) params.append('status', filter.status);

    window.location.href = route(`relatorios.export.${type}`) + '?' + params.toString();
};

const relatorios = [
    {
        id: 'associados',
        title: 'Relatório de Associados / Clientes',
        description: 'Exportação completa dos cooperados/clientes com formas de pagamento e dados cadastrais.',
        icon: UserGroupIcon,
    },
    {
        id: 'financeiro',
        title: 'Relatório Financeiro',
        description: 'Listagem de lançamentos financeiros (taxas e cotas capital) para controle contábil.',
        icon: BanknotesIcon,
        hasStatus: true,
        statusOptions: [
            { value: '', label: 'Todos os status' },
            { value: 'pago', label: 'Pago' },
            { value: 'pendente', label: 'Pendente' },
            { value: 'atrasado', label: 'Atrasado' }
        ]
    },
    {
        id: 'agendamentos',
        title: 'Relatório de Agendamentos e Exames',
        description: 'Relatório detalhado de consultas e exames solicitados, aprovados e realizados.',
        icon: ClipboardDocumentListIcon,
        hasStatus: true,
        statusOptions: [
            { value: '', label: 'Todos os status' },
            { value: 'pendente', label: 'Pendente' },
            { value: 'aprovado', label: 'Aprovado' },
            { value: 'realizado', label: 'Realizado' },
            { value: 'cancelado', label: 'Cancelado' }
        ]
    },
    {
        id: 'prestadores',
        title: 'Relatório de Prestadores e Credenciados',
        description: 'Relação de clínicas, médicos e laboratórios parceiros cadastrados no sistema.',
        icon: DocumentArrowDownIcon,
    },
    {
        id: 'dependentes',
        title: 'Relatório de Dependentes',
        description: 'Relação de dependentes legais e não-legais, incluindo autorização de acesso ao portal.',
        icon: UserGroupIcon,
    },
    {
        id: 'adesoes',
        title: 'Relatório de Adesões',
        description: 'Estatísticas e dados de adesões recentes de planos e cartões.',
        icon: DocumentArrowDownIcon,
    }
];
</script>

<template>
    <Head title="Central de Relatórios" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-black text-xl text-[var(--text-heading)] leading-tight uppercase tracking-widest">
                Central de Relatórios
            </h2>
            <p class="text-sm text-[var(--text-muted)] mt-1 font-medium">Exporte dados operacionais e financeiros em formato Excel (.xlsx)</p>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div 
                        v-for="relatorio in relatorios" 
                        :key="relatorio.id" 
                        class="bg-[var(--bg-card)] rounded-2xl shadow-sm border border-[var(--border-color)]/30 overflow-hidden flex flex-col"
                    >
                        <div class="p-6 border-b border-[var(--border-color)]/20 flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-primary-dynamic/10 flex items-center justify-center text-primary-dynamic shrink-0">
                                <component :is="relatorio.icon" class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-[var(--text-heading)]">{{ relatorio.title }}</h3>
                                <p class="text-sm text-[var(--text-muted)] mt-1 leading-relaxed">{{ relatorio.description }}</p>
                            </div>
                        </div>
                        
                        <div class="p-6 bg-[var(--bg-main)]/50 grow flex flex-col justify-between space-y-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel value="Data Início" class="!text-[10px]" />
                                    <TextInput type="date" v-model="filters[relatorio.id].data_inicio" class="w-full !py-2 text-sm mt-1" />
                                </div>
                                <div>
                                    <InputLabel value="Data Fim" class="!text-[10px]" />
                                    <TextInput type="date" v-model="filters[relatorio.id].data_fim" class="w-full !py-2 text-sm mt-1" />
                                </div>
                                <div v-if="relatorio.hasStatus" class="col-span-2">
                                    <InputLabel value="Status" class="!text-[10px]" />
                                    <select v-model="filters[relatorio.id].status" class="mt-1 block w-full rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-slate-200 focus:border-primary-dynamic focus:ring focus:ring-primary-dynamic/20 text-sm py-2">
                                        <option v-for="opt in relatorio.statusOptions" :key="opt.value" :value="opt.value">
                                            {{ opt.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            
                            <PrimaryButton @click="exportExcel(relatorio.id)" class="w-full justify-center gap-2">
                                <DocumentArrowDownIcon class="w-5 h-5" />
                                Exportar Excel
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
