<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { 
    UserGroupIcon,
    ChartBarIcon,
    PlusIcon,
    TrashIcon,
    XMarkIcon,
    UserIcon
} from '@heroicons/vue/24/outline';
import { ref } from 'vue';

const props = defineProps({
    cotas: Object,
    associados: Array,
});

const showModal = ref(false);
const form = useForm({
    associado_id: '',
    valor_quota: 100,
    qtd_quotas: 1,
    saldo: 100,
    tipo_movimento: 'integralizacao',
});

const updateSaldo = () => {
    form.saldo = form.valor_quota * form.qtd_quotas;
};

const submit = () => {
    form.post(route('financeiro.capital.store'), {
        onSuccess: () => { showModal.value = false; form.reset(); }
    });
};

const deleteCapital = (id) => {
    if (confirm('Excluir este registro de capital?')) {
        form.delete(route('financeiro.capital.destroy', id));
    }
};
</script>

<template>
    <Head title="Cota Capital" />

    <AuthenticatedLayout>
        <div class="py-6 sm:py-8 lg:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 lg:mb-12">
                    <div>
                        <Breadcrumbs :items="[{ label: 'Financeiro', href: '/financeiro' }, { label: 'Capital Social' }]" />
                        <h1 class="text-2xl lg:text-3xl font-black text-[var(--text-heading)] tracking-tighter mt-2">
                            Cota Capital Social
                        </h1>
                    </div>
                    <div>
                        <PrimaryButton @click="showModal = true" class="shadow-xl">
                            <PlusIcon class="w-4 h-4 mr-2" />
                            Nova Integralização
                        </PrimaryButton>
                    </div>
                </div>
                
                <!-- Global Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="zynix-card overflow-hidden !p-8 bg-primary-dynamic relative">
                         <!-- Decorative element -->
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                        
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-6">
                                <div class="w-12 h-12 p-3 rounded-2xl bg-white/20 text-white shadow-inner">
                                    <ChartBarIcon class="w-full h-full" />
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-white/70">Capital Total</span>
                            </div>
                            <p class="text-4xl font-black text-white tracking-tighter mb-2">
                                R$ {{ cotas.data.reduce((acc, c) => acc + parseFloat(c.saldo), 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                            </p>
                            <p class="text-xs font-bold text-white/60 uppercase tracking-widest">Soma de todo capital integralizado</p>
                        </div>
                    </div>

                    <div class="zynix-card group hover:scale-[1.02] transition-all duration-300 !p-8">
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-12 h-12 p-3 rounded-2xl bg-primary-dynamic/10 text-primary-dynamic shadow-inner group-hover:bg-primary-dynamic/20 transition-colors">
                                <UserGroupIcon class="w-full h-full" />
                            </div>
                            <span class="px-2 py-1 bg-primary-dynamic/10 text-primary-dynamic text-[10px] font-black uppercase tracking-widest rounded-lg border border-primary-dynamic/20">Cooperados</span>
                        </div>
                        <p class="text-4xl font-black text-[var(--text-heading)] tracking-tighter mb-2">{{ associados.length }}</p>
                        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Base atualizada de associados</p>
                    </div>
                </div>

                <!-- Capital List -->
                <div class="zynix-card overflow-hidden !p-0">
                    <div class="overflow-x-auto">
                        <table class="zynix-table w-full">
                            <thead>
                                <tr>
                                    <th class="pl-8">Associado</th>
                                    <th class="text-center">Quotas</th>
                                    <th class="text-center">Valor Unit.</th>
                                    <th class="text-center">Saldo Total</th>
                                    <th class="text-right pr-8">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in cotas.data" :key="item.id">
                                    <td class="pl-8">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-9 h-9 p-2 rounded-xl bg-primary-dynamic/10 text-primary-dynamic shadow-inner">
                                                <UserIcon class="w-full h-full" />
                                            </div>
                                            <div class="ml-4">
                                                <div class="font-black text-[var(--text-heading)] tracking-tight">{{ item.associado.nome }}</div>
                                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-tight italic">{{ item.tipo_movimento || 'Integralização' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center font-black text-[var(--text-heading)]">
                                        {{ item.qtd_quotas }}
                                    </td>
                                    <td class="text-center text-xs font-medium text-slate-500 dark:text-slate-400">
                                        R$ {{ parseFloat(item.valor_quota).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                                    </td>
                                    <td class="text-center font-black text-primary-dynamic tracking-tight">
                                        R$ {{ parseFloat(item.saldo).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                                    </td>
                                    <td class="text-right pr-8">
                                        <button @click="deleteCapital(item.id)" class="p-2 text-slate-400 hover:text-rose-500 transition-all rounded-full hover:bg-rose-500/10">
                                            <TrashIcon class="w-4 h-4" />
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="cotas.data.length === 0">
                                    <td colspan="5" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="p-4 rounded-2xl bg-primary-dynamic/10 text-primary-dynamic mb-4">
                                                <ChartBarIcon class="w-8 h-8" />
                                            </div>
                                            <p class="font-black text-slate-400 uppercase tracking-widest text-xs">Nenhuma integralização registrada</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <Modal :show="showModal" @close="showModal = false" max-width="lg">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <p class="text-[10px] font-black text-primary-dynamic uppercase tracking-[0.2em] mb-1">Capital Social</p>
                        <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tighter">Registrar Capital</h2>
                    </div>
                    <button @click="showModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <div class="space-y-6">
                        <div>
                            <InputLabel value="Associado *" />
                            <select v-model="form.associado_id" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-main)] text-[var(--text-heading)] focus:ring-primary-dynamic focus:border-primary-dynamic py-3 text-sm font-medium" required>
                                <option value="" disabled>Selecione o cooperado</option>
                                <option v-for="a in associados" :key="a.id" :value="a.id">{{ a.nome }}</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <InputLabel value="Qtd Quotas *" />
                                <TextInput v-model="form.qtd_quotas" @input="updateSaldo" type="number" class="block w-full bg-[var(--bg-main)]" required />
                            </div>
                            <div>
                                <InputLabel value="Valor Quota *" />
                                <TextInput v-model="form.valor_quota" @input="updateSaldo" type="number" step="0.01" class="block w-full bg-[var(--bg-main)]" required />
                            </div>
                        </div>

                        <div class="p-6 bg-[var(--bg-main)] rounded-2xl border border-[var(--border-color)]">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">Saldo a Lançar</span>
                                <span class="text-2xl font-black text-primary-dynamic tracking-tighter">
                                    R$ {{ form.saldo.toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 flex items-center justify-end space-x-3 border-t border-[var(--border-color)]">
                        <SecondaryButton @click="showModal = false" type="button" class="!rounded-full !py-4 !px-8">Cancelar</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing" class="!rounded-full !py-4 !px-8 shadow-xl shadow-primary-dynamic/20">
                            Salvar Capital
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
