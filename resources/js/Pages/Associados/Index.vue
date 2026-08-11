<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    PlusIcon, 
    PencilSquareIcon, 
    TrashIcon, 
    UserIcon,
    MagnifyingGlassIcon,
    ShareIcon,
    ClipboardIcon,
    QrCodeIcon,
    CheckIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';
import { ref, onMounted } from 'vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';

const props = defineProps({
    associados: Object,
});

const form = useForm({});
const showQrModal = ref(false);
const copied = ref(false);
const publicLink = ref('');

onMounted(() => {
    // Generate the full public link based on current domain
    publicLink.value = `${window.location.protocol}//${window.location.host}/adesao`;
});

function copyLink() {
    navigator.clipboard.writeText(publicLink.value);
    copied.value = true;
    setTimeout(() => copied.value = false, 2000);
}

function deleteAssociado(id) {
    if (window.confirm('Tem certeza que deseja excluir este associado?')) {
        form.delete(route('associados.destroy', id), {
            preserveScroll: true,
            onSuccess: () => alert('Associado excluído com sucesso!')
        });
    }
}
</script>

<template>
    <Head title="Gestão de Associados" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <Breadcrumbs :items="[{ label: 'Associados' }]" />
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight mt-1">
                        Central de {{ $page.props.tenant?.tipo === 'fundacao' ? 'Clientes' : 'Associados' }}
                    </h2>
                    <p class="text-sm text-[var(--text-muted)] mt-0.5">Gerencie os cadastros e status da sua unidade.</p>
                </div>
                <div>
                    <Link :href="route('associados.create')">
                        <PrimaryButton class="shadow-xl">
                            <PlusIcon class="w-4 h-4 mr-2" />
                            Novo Cadastro
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Public Onboarding Link Banner -->
            <div class="p-1 bg-gradient-to-r from-primary-dynamic/20 to-transparent rounded-2xl border border-[var(--border-color)] bg-[var(--bg-card)] shadow-sm opacity-90">
                <div class="p-5 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="hidden sm:flex p-3 bg-primary-dynamic/10 text-primary-dynamic rounded-xl shadow-inner">
                            <ShareIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="font-black text-[var(--text-heading)] leading-tight tracking-tight">Link de Adesão Digital</h3>
                            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">Compartilhe com novos cooperados</p>
                            <div class="mt-2 flex items-center gap-2 font-mono text-[10px] bg-slate-50 dark:bg-slate-800 border border-[var(--border-color)] px-3 py-1.5 rounded-lg text-primary-dynamic select-all">
                                {{ publicLink }}
                            </div>
                        </div>
                    </div>
                        <div class="flex items-center gap-2 w-full md:w-auto">
                            <button 
                                @click="copyLink"
                                :class="copied ? 'bg-emerald-500 shadow-emerald-200' : 'bg-primary-dynamic shadow-primary-dynamic/20'"
                                class="flex-1 md:flex-none inline-flex items-center justify-center px-6 py-3 text-[10px] font-black text-white rounded-full shadow-xl hover:scale-105 active:scale-95 transition-all uppercase tracking-widest"
                            >
                                <component :is="copied ? CheckIcon : ClipboardIcon" class="w-3.5 h-3.5 mr-2" />
                                {{ copied ? 'Copiado!' : 'Copiar Link' }}
                            </button>
                            <button 
                                @click="showQrModal = true"
                                class="flex-1 md:flex-none inline-flex items-center justify-center px-6 py-3 text-[10px] font-black text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-[var(--border-color)] rounded-full shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 active:scale-95 transition-all uppercase tracking-widest"
                            >
                                <QrCodeIcon class="w-3.5 h-3.5 mr-2" />
                                QR Code
                            </button>
                        </div>
                    </div>
                </div>

                <div class="zynix-table-wrapper">
                    <!-- Filters/Search Area -->
                    <div class="zynix-list-header flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="relative flex-grow max-w-md">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                    <MagnifyingGlassIcon class="w-4 h-4 text-slate-400" />
                                </div>
                                <input 
                                    type="text" 
                                    placeholder="Buscar por nome ou CPF..." 
                                    class="block w-full py-3 pl-12 pr-4 text-[11px] font-black uppercase tracking-widest bg-slate-100 dark:bg-slate-800/50 border-none rounded-full text-[var(--text-main)] placeholder:text-slate-400 focus:ring-1 focus:ring-primary-dynamic transition-all"
                                />
                            </div>
                        </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="zynix-table w-full">
                            <thead>
                                <tr>
                                    <th class="pl-8">Associado</th>
                                    <th class="text-center">CPF</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Adesão</th>
                                    <th class="text-right pr-8">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="associado in associados.data" :key="associado.id">
                                    <td class="pl-8">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-9 h-9 p-2 rounded-xl bg-primary-dynamic/10 text-primary-dynamic shadow-inner">
                                                <UserIcon class="w-full h-full" />
                                            </div>
                                            <div class="ml-4">
                                                <div class="font-black text-[var(--text-heading)] tracking-tight">{{ associado.nome }}</div>
                                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-tight">#{{ associado.id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        {{ associado.cpf }}
                                    </td>
                                    <td class="text-center">
                                        <span 
                                            :class="{
                                                'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-500/20': associado.status === 'ativo',
                                                'bg-slate-50 dark:bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-100 dark:border-slate-500/20': associado.status === 'inativo',
                                                'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-500/20': associado.status === 'pendente'
                                            }"
                                            class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider border rounded-full inline-flex items-center"
                                        >
                                            {{ associado.status }}
                                        </span>
                                    </td>
                                    <td class="text-center font-medium">
                                        {{ associado.data_adesao ? new Date(associado.data_adesao).toLocaleDateString('pt-BR') : 'N/A' }}
                                    </td>
                                    <td class="text-right pr-8 whitespace-nowrap">
                                        <div class="flex items-center justify-end space-x-1">
                                            <Link 
                                                :href="route('associados.edit', associado.id)"
                                                class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-xl transition-all"
                                                title="Editar"
                                            >
                                                <PencilSquareIcon class="w-4 h-4" />
                                            </Link>
                                            <button 
                                                @click="deleteAssociado(associado.id)" 
                                                class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-all"
                                                title="Excluir"
                                            >
                                                <TrashIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="associados.data.length === 0">
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="p-4 rounded-2xl bg-primary-light mb-4">
                                                <UserIcon class="w-8 h-8" />
                                            </div>
                                            <p class="font-bold text-slate-500">Nenhum associado encontrado</p>
                                            <p class="text-xs text-slate-400 mt-1">Cadastre o primeiro cooperado para começar.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Placeholder -->
                    <div class="p-6 border-t border-[var(--border-color)] flex items-center justify-between" v-if="associados.data.length > 0">
                         <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                             Mostrando {{ associados.from }}-{{ associados.to }} de {{ associados.total }}
                         </div>
                          <div class="flex items-center gap-2">
                              <SecondaryButton class="!px-5 !py-2 !rounded-full !text-[10px] !font-black !uppercase !tracking-widest">
                                  <ChevronLeftIcon class="w-3 h-3 mr-1" /> Anterior
                              </SecondaryButton>
                              <SecondaryButton class="!px-5 !py-2 !rounded-full !text-[10px] !font-black !uppercase !tracking-widest">
                                  Próximo <ChevronRightIcon class="w-3 h-3 ml-1" />
                              </SecondaryButton>
                          </div>
                    </div>
                </div>
        </div>
    </AuthenticatedLayout>

    <!-- QR Code Modal -->
    <Modal :show="showQrModal" @close="showQrModal = false" max-width="sm">
        <div class="p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <p class="text-[10px] font-black text-primary-dynamic uppercase tracking-[0.2em] mb-1">Compartilhamento</p>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tighter">QR Code de Adesão</h2>
                </div>
                <button @click="showQrModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>

            <p class="text-sm text-slate-500 dark:text-slate-400 mb-8 font-medium leading-relaxed italic">Aponte a câmera do celular para abrir o formulário de adesão digital da cooperativa.</p>
            
            <div class="mx-auto w-48 h-48 bg-white p-4 rounded-2xl border-4 border-slate-50 dark:border-slate-800 shadow-inner flex items-center justify-center mb-8">
                <img 
                    v-if="publicLink"
                    :src="`https://api.qrserver.com/v1/create-qr-code/?size=256x256&data=${encodeURIComponent(publicLink)}`" 
                    alt="QR Code"
                    class="w-full h-full object-contain"
                />
            </div>

            <div class="bg-[var(--bg-main)] p-5 rounded-2xl border border-[var(--border-color)] mb-8">
                <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-2">Link Direto</p>
                <p class="text-[11px] font-mono font-medium text-primary-dynamic truncate select-all">{{ publicLink }}</p>
            </div>

            <SecondaryButton @click="showQrModal = false" class="w-full justify-center !py-4 !rounded-full !text-[11px] !font-black !uppercase !tracking-widest text-slate-600">
                <XMarkIcon class="w-4 h-4 mr-2" /> Fechar Janela
            </SecondaryButton>
        </div>
    </Modal>
</template>

<style scoped>
</style>
