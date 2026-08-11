<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import CooperadoLayout from '@/Layouts/CooperadoLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { 
    IdentificationIcon, 
    CheckBadgeIcon, 
    ArrowDownTrayIcon,
    UsersIcon,
    UserIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    associado: { type: Object, required: true },
    planosDisponiveis: { type: Array, default: () => [] }
});

const page = usePage();
const tenant = computed(() => page.props.tenant);
const primaryColor = computed(() => tenant.value?.primary_color || '#2563eb');

const hasCarteirinha = computed(() => props.associado.carteirinha_valida === true);
const qrCanvas = ref(null);

function drawQRCode() {
    if (!qrCanvas.value || !props.associado.carteirinha_numero) return;
    
    const ctx = qrCanvas.value.getContext('2d');
    const size = 100;
    const padding = 4;
    const modules = 15;
    const moduleSize = (size - padding * 2) / modules;
    
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, size, size);
    
    // Hash function for visual representation
    const text = props.associado.carteirinha_numero;
    let hash = 0;
    for (let i = 0; i < text.length; i++) {
        hash = ((hash << 5) - hash) + text.charCodeAt(i);
        hash |= 0; 
    }
    
    ctx.fillStyle = '#000000';
    // Draw positioning patterns
    const drawPattern = (x, y) => {
        ctx.fillRect(padding + x * moduleSize, padding + y * moduleSize, moduleSize * 3, moduleSize * 3);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(padding + (x+1) * moduleSize, padding + (y+1) * moduleSize, moduleSize, moduleSize);
        ctx.fillStyle = '#000000';
    };
    drawPattern(0, 0);
    drawPattern(12, 0);
    drawPattern(0, 12);
    
    // Draw data
    for (let r = 0; r < modules; r++) {
        for (let c = 0; c < modules; c++) {
            if ((r < 4 && c < 4) || (r < 4 && c > 10) || (r > 10 && c < 4)) continue;
            
            const bit = (hash ^ (r * 17 + c * 31)) & 1;
            if (bit) {
                ctx.fillRect(padding + c * moduleSize, padding + r * moduleSize, moduleSize, moduleSize);
            }
        }
    }
}

onMounted(() => {
    if (hasCarteirinha.value) {
        nextTick(() => drawQRCode());
    }
});

const form = useForm({
    tipo: 'individual',
    plano_id: ''
});

function submitRequest() {
    form.post('/cooperado/carteirinha/solicitar', {
        preserveScroll: true,
        onSuccess: () => {
            // Handle success
        }
    });
}
</script>

<template>
    <Head title="Minha Carteirinha" />

    <CooperadoLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary-light flex items-center justify-center text-primary-dynamic">
                    <IdentificationIcon class="w-6 h-6" />
                </div>
                <div>
                    <h2 class="text-2xl font-black text-[var(--text-heading)] tracking-tight">Carteirinha Digital</h2>
                    <p class="text-[12px] text-[var(--text-muted)] mt-0.5 font-bold">
                        Sua identificação oficial para uso da rede credenciada.
                    </p>
                </div>
            </div>
        </template>

        <div v-if="hasCarteirinha" class="max-w-3xl mx-auto space-y-8 pb-12">
            <!-- Digital Card -->
            <div class="carteirinha-card relative overflow-hidden rounded-2xl shadow-xl p-8 text-white">
                <div class="relative z-10 flex flex-col h-full justify-between gap-6">
                    <!-- Top section -->
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-black tracking-tight">{{ tenant?.name || 'Siscoop3F' }}</h3>
                            <p class="text-[10px] font-bold uppercase tracking-widest opacity-80 mt-1">Assistência à Saúde</p>
                        </div>
                        <div class="flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full">
                            <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                            <span class="text-[9px] font-black uppercase tracking-widest text-emerald-50">Ativa</span>
                        </div>
                    </div>

                    <!-- Middle section -->
                    <div class="flex items-center justify-between mt-8">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest opacity-80 mb-1">Nome do Beneficiário</p>
                            <p class="text-2xl font-black tracking-tight">{{ associado.nome }}</p>
                        </div>
                        <div class="bg-white p-2 rounded-xl shrink-0">
                            <canvas ref="qrCanvas" width="100" height="100" class="block w-20 h-20 rounded-lg"></canvas>
                        </div>
                    </div>

                    <!-- Bottom section -->
                    <div class="grid grid-cols-3 gap-4 mt-6 pt-6 border-t border-white/20">
                        <div>
                            <p class="text-[9px] font-bold uppercase tracking-widest opacity-80 mb-1">Carteira Nº</p>
                            <p class="text-sm font-black">{{ associado.carteirinha_numero }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold uppercase tracking-widest opacity-80 mb-1">Plano</p>
                            <p class="text-sm font-black">{{ associado.plano?.nome || 'Padrão' }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold uppercase tracking-widest opacity-80 mb-1">Validade</p>
                            <p class="text-sm font-black">{{ associado.carteirinha_validade || 'Indeterminada' }}</p>
                        </div>
                    </div>
                </div>
                <!-- Background decoration -->
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-20 -bottom-20 w-48 h-48 bg-black/10 rounded-full blur-3xl pointer-events-none"></div>
            </div>

            <!-- Actions -->
            <div class="flex justify-center">
                <PrimaryButton class="flex items-center gap-2">
                    <ArrowDownTrayIcon class="w-4 h-4" />
                    Baixar PDF
                </PrimaryButton>
            </div>

            <!-- Details Section -->
            <div class="zynix-card">
                <div class="zynix-card-header mb-6">
                    <h4 class="text-[11px] font-black text-[var(--text-heading)] uppercase tracking-widest">Informações Adicionais</h4>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Data de Nascimento</p>
                        <p class="text-sm font-bold text-[var(--text-heading)]">{{ associado.nascimento || 'Não informada' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Data de Adesão</p>
                        <p class="text-sm font-bold text-[var(--text-heading)]">{{ associado.data_adesao || 'Não informada' }}</p>
                    </div>
                </div>

                <div v-if="associado.plano?.tipo === 'familiar' && associado.dependentes?.length" class="mt-8 pt-8 border-t border-[var(--border-color)]">
                    <h5 class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-4 flex items-center gap-2">
                        <UsersIcon class="w-4 h-4" /> Dependentes Inclusos
                    </h5>
                    <div class="space-y-3">
                        <div v-for="dep in associado.dependentes" :key="dep.id" class="flex justify-between items-center bg-slate-50 dark:bg-white/[0.02] p-3 rounded-lg border border-[var(--border-color)]">
                            <div>
                                <p class="text-xs font-black text-[var(--text-heading)]">{{ dep.nome }}</p>
                                <p class="text-[9px] font-bold text-[var(--text-muted)] uppercase mt-0.5">{{ dep.parentesco }}</p>
                            </div>
                            <span class="px-2 py-1 bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 text-[9px] font-black uppercase tracking-widest rounded">Ativo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="max-w-3xl mx-auto space-y-8 pb-12">
            <!-- Solicitation Call to Action -->
            <div class="text-center py-10 px-4 bg-slate-50 dark:bg-white/[0.02] rounded-2xl border border-[var(--border-color)]">
                <div class="w-16 h-16 bg-primary-light text-primary-dynamic rounded-full flex items-center justify-center mx-auto mb-4">
                    <IdentificationIcon class="w-8 h-8" />
                </div>
                <h3 class="text-xl font-black text-[var(--text-heading)] mb-2">Solicite sua Carteirinha Digital</h3>
                <p class="text-sm text-[var(--text-muted)] max-w-lg mx-auto">
                    Você ainda não possui uma carteirinha ativa. Escolha um dos nossos planos de assistência para ter acesso a toda rede de prestadores com valores diferenciados.
                </p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitRequest" class="zynix-card space-y-6">
                <div>
                    <h4 class="text-[11px] font-black text-[var(--text-heading)] uppercase tracking-widest mb-4">Modalidade</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <label 
                            class="flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all"
                            :class="form.tipo === 'individual' ? 'border-primary-dynamic bg-primary-light/50' : 'border-[var(--border-color)] hover:border-slate-300'"
                        >
                            <input type="radio" v-model="form.tipo" value="individual" class="sr-only">
                            <UserIcon class="w-6 h-6 mb-2" :class="form.tipo === 'individual' ? 'text-primary-dynamic' : 'text-slate-400'" />
                            <span class="text-sm font-black text-[var(--text-heading)]">Individual</span>
                            <span class="text-[10px] text-[var(--text-muted)] mt-1 font-bold">Apenas para o titular</span>
                        </label>
                        <label 
                            class="flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all"
                            :class="form.tipo === 'familiar' ? 'border-primary-dynamic bg-primary-light/50' : 'border-[var(--border-color)] hover:border-slate-300'"
                        >
                            <input type="radio" v-model="form.tipo" value="familiar" class="sr-only">
                            <UsersIcon class="w-6 h-6 mb-2" :class="form.tipo === 'familiar' ? 'text-primary-dynamic' : 'text-slate-400'" />
                            <span class="text-sm font-black text-[var(--text-heading)]">Familiar</span>
                            <span class="text-[10px] text-[var(--text-muted)] mt-1 font-bold">Titular e dependentes</span>
                        </label>
                    </div>
                </div>

                <div>
                    <h4 class="text-[11px] font-black text-[var(--text-heading)] uppercase tracking-widest mb-4">Escolha o Plano</h4>
                    <div class="space-y-4">
                        <label 
                            v-for="plano in planosDisponiveis" :key="plano.id"
                            class="flex items-center justify-between p-4 rounded-xl border-2 cursor-pointer transition-all"
                            :class="form.plano_id === plano.id ? 'border-primary-dynamic bg-primary-light/50' : 'border-[var(--border-color)] hover:border-slate-300'"
                        >
                            <div class="flex items-center gap-4">
                                <input type="radio" v-model="form.plano_id" :value="plano.id" class="sr-only">
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                     :class="form.plano_id === plano.id ? 'border-primary-dynamic' : 'border-slate-300'">
                                    <div v-if="form.plano_id === plano.id" class="w-2.5 h-2.5 rounded-full bg-primary-dynamic"></div>
                                </div>
                                <div>
                                    <span class="text-sm font-black text-[var(--text-heading)]">{{ plano.nome }}</span>
                                    <p class="text-[10px] text-[var(--text-muted)] mt-0.5 max-w-sm">{{ plano.descricao }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-black text-[var(--text-heading)]">R$ {{ (plano.valor_mensalidade || 0).toFixed(2) }}<span class="text-[10px] text-[var(--text-muted)]">/mês</span></p>
                                <p v-if="plano.valor_adesao" class="text-[9px] font-bold text-[var(--text-muted)] uppercase tracking-widest">Adesão: R$ {{ plano.valor_adesao.toFixed(2) }}</p>
                            </div>
                        </label>
                    </div>
                    <p v-if="form.errors?.plano_id" class="text-xs text-rose-500 mt-2 font-bold">{{ form.errors.plano_id }}</p>
                </div>

                <div class="pt-6 border-t border-[var(--border-color)] flex justify-end">
                    <PrimaryButton :disabled="form.processing" class="w-full sm:w-auto">
                        Solicitar Adesão ao Plano
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </CooperadoLayout>
</template>

<style scoped>
.carteirinha-card {
    background: linear-gradient(135deg, v-bind(primaryColor), #1e293b);
}
</style>
