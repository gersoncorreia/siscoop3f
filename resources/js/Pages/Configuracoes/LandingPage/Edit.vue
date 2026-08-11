<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { 
    PaintBrushIcon, 
    PhotoIcon, 
    CheckCircleIcon,
    SwatchIcon,
    ArrowUpTrayIcon,
    CheckBadgeIcon,
    GlobeAltIcon,
    ChevronDownIcon,
    PlusIcon,
    TrashIcon,
    PhoneIcon
} from '@heroicons/vue/24/outline';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    _method: 'POST',
    theme_preset: props.settings.theme_preset || 'emerald',
    primary_color: props.settings.primary_color || '#004b23',
    secondary_color: props.settings.secondary_color || '#ffb703',
    
    section_hero_enabled: props.settings.section_hero_enabled ?? true,
    hero_title: props.settings.hero_title || '',
    hero_subtitle: props.settings.hero_subtitle || '',
    logo: null,
    hero_image: null,
    
    section_about_enabled: props.settings.section_about_enabled ?? true,
    about_title: props.settings.about_title || '',
    about_text: props.settings.about_text || '',
    
    section_features_enabled: props.settings.section_features_enabled ?? true,
    features: props.settings.features || [],
    
    section_contact_enabled: props.settings.section_contact_enabled ?? true,
    contact_email: props.settings.contact_email || '',
    contact_phone: props.settings.contact_phone || '',
    contact_address: props.settings.contact_address || '',
    social_facebook: props.settings.social_facebook || '',
    social_instagram: props.settings.social_instagram || '',
    social_linkedin: props.settings.social_linkedin || '',
});

const presets = {
    emerald: { name: 'Verde Floresta (Padrão)', primary: '#004b23', secondary: '#ffb703' },
    sky: { name: 'Azul Celeste', primary: '#0284c7', secondary: '#f97316' },
    sunset: { name: 'Pôr do Sol', primary: '#db2777', secondary: '#fbbf24' },
    indigo: { name: 'Índigo Nobre', primary: '#4f46e5', secondary: '#06b6d4' },
    custom: { name: 'Customizado (Escolha livre)', primary: '#004b23', secondary: '#ffb703' }
};

const selectPreset = (key) => {
    form.theme_preset = key;
    if (key !== 'custom') {
        form.primary_color = presets[key].primary;
        form.secondary_color = presets[key].secondary;
    }
};

const addFeature = () => {
    if (form.features.length < 6) {
        form.features.push({
            icon: 'ShieldCheckIcon',
            title: 'Novo Diferencial',
            description: 'Breve descrição sobre a segurança ou vantagem para o cooperado.'
        });
    }
};

const removeFeature = (index) => {
    form.features.splice(index, 1);
};

import swal from '@/Utils/swal';

const onLogoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            swal.error('O logotipo não deve ser maior que 5MB.', 'Arquivo muito grande');
            e.target.value = '';
            form.logo = null;
            return;
        }
        if (!file.type.startsWith('image/')) {
            swal.error('O arquivo selecionado deve ser uma imagem.', 'Formato inválido');
            e.target.value = '';
            form.logo = null;
            return;
        }
        form.logo = file;
    }
};

const onHeroImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            swal.error('A imagem do banner não deve ser maior que 5MB.', 'Arquivo muito grande');
            e.target.value = '';
            form.hero_image = null;
            return;
        }
        if (!file.type.startsWith('image/')) {
            swal.error('O arquivo selecionado deve ser uma imagem.', 'Formato inválido');
            e.target.value = '';
            form.hero_image = null;
            return;
        }
        form.hero_image = file;
    }
};

const submit = () => {
    form.post(route('configuracoes.landing-page.update'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // Sucesso
        }
    });
};
</script>

<template>
    <Head title="Configurações da Landing Page" />

    <AuthenticatedLayout>
        <div class="py-6 sm:py-8 lg:py-12">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 lg:mb-12">
                    <div>
                        <Breadcrumbs :items="[{ label: 'Ajustes', href: route('configuracoes.index') }, { label: 'Landing Page' }]" />
                        <h1 class="text-2xl lg:text-3xl font-black text-[var(--text-heading)] tracking-tighter mt-2">
                            Configuração do Site (OnePage)
                        </h1>
                    </div>
                </div>

                <div v-if="$page.props.flash?.success" class="mb-8 p-4 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20 rounded-2xl font-bold flex items-center gap-3 animate-in fade-in slide-in-from-top-4 duration-500">
                    <CheckBadgeIcon class="w-5 h-5" />
                    {{ $page.props.flash.success }}
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Seção 1: Identidade e Cores -->
                    <div class="zynix-card overflow-hidden !p-0">
                        <div class="p-8 border-b border-[var(--border-color)] flex items-center gap-4 bg-primary-dynamic/5">
                            <div class="p-3 bg-primary-dynamic/10 text-primary-dynamic rounded-xl">
                                <PaintBrushIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="font-black text-[var(--text-heading)] text-lg">Tema e Cores da Landing Page</h3>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mt-1">Defina a paleta de cores institucional pública</p>
                            </div>
                        </div>

                        <div class="p-8 space-y-8">
                            <!-- Preset Selector -->
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-4">Escolha uma Paleta de Cores</label>
                                <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
                                    <button 
                                        v-for="(preset, key) in presets" 
                                        :key="key"
                                        type="button"
                                        @click="selectPreset(key)"
                                        class="p-4 rounded-2xl border-2 text-left transition-all relative flex flex-col justify-between h-28"
                                        :class="form.theme_preset === key ? 'border-[var(--primary)] bg-slate-50/50' : 'border-[var(--border-color)] hover:border-slate-300 bg-white'"
                                    >
                                        <span class="text-xs font-bold text-slate-800">{{ preset.name }}</span>
                                        <div class="flex items-center gap-2 mt-4" v-if="key !== 'custom'">
                                            <span :style="{ backgroundColor: preset.primary }" class="w-5 h-5 rounded-full border border-white shadow-sm"></span>
                                            <span :style="{ backgroundColor: preset.secondary }" class="w-5 h-5 rounded-full border border-white shadow-sm"></span>
                                        </div>
                                        <div class="flex items-center gap-1 mt-4" v-else>
                                            <SwatchIcon class="w-5 h-5 text-slate-400" />
                                            <span class="text-[10px] font-semibold text-slate-400">Livre</span>
                                        </div>
                                    </button>
                                </div>
                                                   
                                <!-- Custom Colors Picker -->
                                <div v-if="form.theme_preset === 'custom'" class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4 border-t border-[var(--border-color)]/50 animate-in fade-in duration-300">
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">Cor Primária</label>
                                        <div class="flex items-center space-x-4">
                                            <input v-model="form.primary_color" type="color" class="w-12 h-12 rounded-full border-none p-0 cursor-pointer shadow" />
                                            <input v-model="form.primary_color" type="text" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-body)] py-3 px-4 font-mono text-sm uppercase font-bold" />
                                        </div>
                                        <span v-if="form.errors.primary_color" class="text-xs font-bold text-red-500 mt-2 block">{{ form.errors.primary_color }}</span>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">Cor Secundária (Destaques)</label>
                                        <div class="flex items-center space-x-4">
                                            <input v-model="form.secondary_color" type="color" class="w-12 h-12 rounded-full border-none p-0 cursor-pointer shadow" />
                                            <input v-model="form.secondary_color" type="text" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-body)] py-3 px-4 font-mono text-sm uppercase font-bold" />
                                        </div>
                                        <span v-if="form.errors.secondary_color" class="text-xs font-bold text-red-500 mt-2 block">{{ form.errors.secondary_color }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seção 2: Seção Hero (Banner Principal) -->
                    <div class="zynix-card overflow-hidden !p-0">
                        <div class="p-8 border-b border-[var(--border-color)] flex items-center justify-between bg-slate-50">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-slate-100 border text-slate-700 rounded-xl">
                                    <GlobeAltIcon class="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 class="font-black text-[var(--text-heading)] text-lg">Seção Hero (Principal)</h3>
                                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mt-1">O primeiro impacto visual da Landing Page</p>
                                </div>
                            </div>
                            <!-- Habilitar/Desabilitar -->
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.section_hero_enabled" class="sr-only peer" />
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--primary)]"></div>
                            </label>
                        </div>

                        <div v-if="form.section_hero_enabled" class="p-8 space-y-6 animate-in fade-in duration-300">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">Título de Impacto (Hero Title)</label>
                                <input v-model="form.hero_title" type="text" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-body)] py-3.5 px-4 text-sm font-bold" />
                                <span v-if="form.errors.hero_title" class="text-xs font-bold text-red-500 mt-2 block">{{ form.errors.hero_title }}</span>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">Subtítulo Explicativo</label>
                                <textarea v-model="form.hero_subtitle" rows="3" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-body)] py-3.5 px-4 text-sm font-bold resize-none"></textarea>
                                <span v-if="form.errors.hero_subtitle" class="text-xs font-bold text-red-500 mt-2 block">{{ form.errors.hero_subtitle }}</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Upload Logo -->
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">Logo do Header</label>
                                    <div class="relative group aspect-[4/1.5] border-2 border-dashed border-[var(--border-color)] hover:border-[var(--primary)]/50 rounded-2xl flex items-center justify-center bg-slate-50/50 overflow-hidden cursor-pointer">
                                        <img v-if="settings.logo_url && !form.logo" :src="settings.logo_url" class="max-h-[60%] object-contain" />
                                        <PhotoIcon v-else-if="!form.logo" class="w-8 h-8 text-slate-200" />
                                        <span v-else class="text-xs font-bold text-emerald-600">Logo Selecionada!</span>
                                        <input type="file" @change="onLogoChange" class="absolute inset-0 opacity-0 cursor-pointer" />
                                    </div>
                                    <span v-if="form.errors.logo" class="text-xs font-bold text-red-500 mt-2 block">{{ form.errors.logo }}</span>
                                    <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-500 mt-2 block">Formatos aceitos: PNG, JPG, WEBP, SVG, GIF. Máx: 5MB (convertido para WebP otimizado).</span>
                                </div>

                                <!-- Upload Hero Image -->
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">Imagem do Banner Principal</label>
                                    <div class="relative group aspect-[4/1.5] border-2 border-dashed border-[var(--border-color)] hover:border-[var(--primary)]/50 rounded-2xl flex items-center justify-center bg-slate-50/50 overflow-hidden cursor-pointer">
                                        <img v-if="settings.hero_image_url && !form.hero_image" :src="settings.hero_image_url" class="max-h-[60%] object-contain" />
                                        <PhotoIcon v-else-if="!form.hero_image" class="w-8 h-8 text-slate-200" />
                                        <span v-else class="text-xs font-bold text-emerald-600">Imagem Selecionada!</span>
                                        <input type="file" @change="onHeroImageChange" class="absolute inset-0 opacity-0 cursor-pointer" />
                                    </div>
                                    <span v-if="form.errors.hero_image" class="text-xs font-bold text-red-500 mt-2 block">{{ form.errors.hero_image }}</span>
                                    <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-500 mt-2 block">Formatos aceitos: PNG, JPG, WEBP, GIF. Máx: 5MB (redimensionado e convertido para WebP).</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seção 3: Seção Sobre -->
                    <div class="zynix-card overflow-hidden !p-0">
                        <div class="p-8 border-b border-[var(--border-color)] flex items-center justify-between bg-slate-50">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-slate-100 border text-slate-700 rounded-xl">
                                    <PhotoIcon class="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 class="font-black text-[var(--text-heading)] text-lg">Seção Quem Somos (Sobre)</h3>
                                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mt-1">A história e propósito da cooperativa</p>
                                </div>
                            </div>
                            <!-- Habilitar/Desabilitar -->
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.section_about_enabled" class="sr-only peer" />
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--primary)]"></div>
                            </label>
                        </div>

                        <div v-if="form.section_about_enabled" class="p-8 space-y-6 animate-in fade-in duration-300">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">Título da Seção</label>
                                <input v-model="form.about_title" type="text" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-body)] py-3.5 px-4 text-sm font-bold" />
                                <span v-if="form.errors.about_title" class="text-xs font-bold text-red-500 mt-2 block">{{ form.errors.about_title }}</span>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">Conteúdo Detalhado</label>
                                <textarea v-model="form.about_text" rows="5" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-body)] py-3.5 px-4 text-sm font-bold resize-none"></textarea>
                                <span v-if="form.errors.about_text" class="text-xs font-bold text-red-500 mt-2 block">{{ form.errors.about_text }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Seção 4: Diferenciais / Benefícios -->
                    <div class="zynix-card overflow-hidden !p-0">
                        <div class="p-8 border-b border-[var(--border-color)] flex items-center justify-between bg-slate-50">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-slate-100 border text-slate-700 rounded-xl">
                                    <SwatchIcon class="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 class="font-black text-[var(--text-heading)] text-lg">Benefícios e Diferenciais</h3>
                                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mt-1">Diferenciais em formato grid (Máximo 6)</p>
                                </div>
                            </div>
                            <!-- Habilitar/Desabilitar -->
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.section_features_enabled" class="sr-only peer" />
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--primary)]"></div>
                            </label>
                        </div>

                        <div v-if="form.section_features_enabled" class="p-8 space-y-6 animate-in fade-in duration-300">
                            <div class="space-y-4">
                                <div v-for="(feature, index) in form.features" :key="index" class="p-6 bg-slate-50 border border-slate-100 rounded-2xl flex flex-col md:flex-row gap-4 items-start relative group">
                                    <button type="button" @click="removeFeature(index)" class="absolute top-4 right-4 text-slate-300 hover:text-red-500 transition-colors">
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                    
                                    <div class="w-full md:w-1/4">
                                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-wider mb-2">Ícone</label>
                                        <select v-model="feature.icon" class="w-full rounded-xl border-[var(--border-color)] bg-white text-xs font-bold py-2.5 px-3">
                                            <option value="ShieldCheckIcon">Escudo (Segurança)</option>
                                            <option value="UserGroupIcon">Pessoas (Comunidade)</option>
                                            <option value="ArrowTrendingUpIcon">Gráfico (Crescimento)</option>
                                        </select>
                                    </div>

                                    <div class="flex-1 space-y-3">
                                        <div>
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-wider mb-2">Título do Benefício</label>
                                            <input v-model="feature.title" type="text" class="w-full rounded-xl border-[var(--border-color)] bg-white text-xs font-bold py-2.5 px-3" />
                                        </div>
                                        <div>
                                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-wider mb-2">Descrição</label>
                                            <input v-model="feature.description" type="text" class="w-full rounded-xl border-[var(--border-color)] bg-white text-xs font-bold py-2.5 px-3" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button 
                                v-if="form.features.length < 6"
                                type="button" 
                                @click="addFeature" 
                                class="inline-flex items-center gap-2 text-xs font-bold text-[var(--primary)] hover:opacity-80 transition-opacity"
                            >
                                <PlusIcon class="w-4 h-4" /> Adicionar Diferencial
                            </button>
                        </div>
                    </div>

                    <!-- Seção 5: Contatos e Redes Sociais -->
                    <div class="zynix-card overflow-hidden !p-0">
                        <div class="p-8 border-b border-[var(--border-color)] flex items-center justify-between bg-slate-50">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-slate-100 border text-slate-700 rounded-xl">
                                    <PhoneIcon class="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 class="font-black text-[var(--text-heading)] text-lg">Informações de Contato</h3>
                                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mt-1">Dados exibidos no rodapé e seção fale conosco</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.section_contact_enabled" class="sr-only peer" />
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--primary)]"></div>
                            </label>
                        </div>

                        <div v-if="form.section_contact_enabled" class="p-8 space-y-6 animate-in fade-in duration-300">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">E-mail de Atendimento</label>
                                    <input v-model="form.contact_email" type="email" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-body)] py-3 px-4 text-xs font-bold" placeholder="suporte@coop.com" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">Telefone/WhatsApp</label>
                                    <input v-model="form.contact_phone" type="text" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-body)] py-3 px-4 text-xs font-bold" placeholder="(00) 00000-0000" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">Endereço Completo</label>
                                    <input v-model="form.contact_address" type="text" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-body)] py-3 px-4 text-xs font-bold" placeholder="Rua Central, 123" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-[var(--border-color)]/50">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">Facebook URL</label>
                                    <input v-model="form.social_facebook" type="text" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-body)] py-3 px-4 text-xs font-bold" placeholder="https://facebook.com/..." />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">Instagram URL</label>
                                    <input v-model="form.social_instagram" type="text" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-body)] py-3 px-4 text-xs font-bold" placeholder="https://instagram.com/..." />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3">LinkedIn URL</label>
                                    <input v-model="form.social_linkedin" type="text" class="w-full rounded-xl border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-body)] py-3 px-4 text-xs font-bold" placeholder="https://linkedin.com/in/..." />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botão de Ação -->
                    <div class="pt-4 pb-12">
                        <PrimaryButton 
                            type="submit" 
                            :disabled="form.processing"
                            class="w-full !py-6 !text-[11px] !tracking-[0.25em] !font-black !rounded-full shadow-2xl transition-all active:scale-[0.98]"
                        >
                            <CheckCircleIcon v-if="!form.processing" class="w-5 h-5 mr-3" />
                            <span v-else class="mr-3 animate-spin border-2 border-white/20 border-t-white rounded-full w-4 h-4"></span>
                            SALVAR CONFIGURAÇÕES DO SITE
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
