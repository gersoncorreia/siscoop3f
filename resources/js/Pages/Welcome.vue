<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { 
    UserGroupIcon, 
    ArrowRightIcon, 
    ChartPieIcon, 
    ShieldCheckIcon, 
    CheckCircleIcon,
    Bars3Icon,
    XMarkIcon,
    DocumentTextIcon,
    SparklesIcon,
    CloudIcon
} from '@heroicons/vue/24/outline';
import { ref } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const mobileMenuOpen = ref(false);

const features = [
  {
    name: 'Visão 360º para Decisões Precisas',
    description: 'Tenha em mãos relatórios contábeis, financeiros e de governança em tempo real. Nossos módulos financeiros são altamente customizáveis, permitindo total clareza na distribuição de sobras, rateios de despesas e prestação de contas do seu ramo específico.',
    icon: ChartPieIcon,
    audience: 'Para a Diretoria'
  },
  {
    name: 'Autonomia e Transparência na Palma da Mão',
    description: 'Esqueça o papel. O cooperado realiza sua adesão 100% online, acompanha seu capital social, baixa demonstrativos e acessa serviços através de um Portal de Autoatendimento intuitivo, seguro e responsivo.',
    icon: UserGroupIcon,
    audience: 'Para o Cooperado'
  },
  {
    name: 'Fim do Trabalho Braçal',
    description: 'Chega de redigitar dados. Automatize faturamentos, geração de guias, gestão de mensalidades e conciliações bancárias. Para o ramo da saúde, oferecemos módulos avançados de billing e gestão ágil de reembolsos.',
    icon: DocumentTextIcon,
    audience: 'Para o Operacional'
  },
];

const technicals = [
  {
    name: 'Arquitetura Modular Adaptável',
    description: 'O coração do Siscoop3F é um núcleo genérico e robusto que se transforma e se adapta rapidamente. Adicione módulos específicos (Saúde, Agrícola, Consumo) conforme a necessidade do seu ramo.',
    icon: ShieldCheckIcon,
  },
  {
    name: 'SaaS Multi-tenancy Puro',
    description: 'Cresça sem se preocupar com infraestrutura. Sua cooperativa ganha um ambiente isolado (tenant) na nuvem, com updates contínuos, alta disponibilidade e risco zero de manter servidores locais físicos.',
    icon: CloudIcon,
  },
  {
    name: 'Automação Guiada por IA',
    description: 'Não somos apenas um ERP. O Siscoop3F utiliza Inteligência Artificial para ler documentos, classificar despesas e sugerir conciliações preditivas, liberando sua equipe para o que realmente importa.',
    icon: SparklesIcon,
  },
]

const faqs = [
  {
    question: 'O sistema serve para o meu ramo de cooperativa?',
    answer: 'Sim! Construímos o Siscoop3F com uma arquitetura de núcleo flexível. Seja Crédito, Saúde, Agrícola, Trabalho ou Consumo, ativamos os módulos sob medida para a sua operação sem você precisar pagar por ferramentas que não usará.',
  },
  {
    question: 'Como a Inteligência Artificial ajuda no meu dia a dia?',
    answer: 'A IA atua lendo documentos anexados, extraindo dados automaticamente e sugerindo baixas e conciliações de remessas/retornos bancários, cortando a digitação humana em até 80%.',
  },
  {
    question: 'A plataforma é segura e atende à LGPD?',
    answer: 'Absolutamente. Nossa arquitetura "Multi-tenancy" garante que o banco de dados da sua cooperativa seja 100% isolado dos demais clientes. Além disso, entregamos trilhas de auditoria completas e termos integrados de LGPD no aceite do cooperado.',
  },
  {
    question: 'Como funciona a contratação e a infraestrutura?',
    answer: 'Operamos 100% como SaaS (Software as a Service) na nuvem. Você não precisa comprar, instalar ou dar manutenção em servidores físicos locais. O acesso é via web e nós cuidamos da segurança, backup e atualização do sistema para você.',
  },
  {
    question: 'A adesão de novos cooperados pelo sistema é válida legalmente?',
    answer: 'Sim, desenvolvemos um fluxo de onboarding 100% digital, onde o cooperado anexa documentos e assina termos que cumprem os requisitos de aceite digital exigidos pelos órgãos reguladores.',
  },
]

const vReveal = {
    mounted: (el, binding) => {
        const delay = binding.value || 0;
        el.classList.add('opacity-0', 'translate-y-10', 'transition-all', 'duration-1000', 'ease-out');
        if (delay) el.style.transitionDelay = `${delay}ms`;
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.remove('opacity-0', 'translate-y-10');
                    entry.target.classList.add('opacity-100', 'translate-y-0');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
        
        observer.observe(el);
    }
}
</script>

<template>
    <Head title="Siscoop3F - SaaS de Gestão para Cooperativas" />

    <div class="bg-gray-50 min-h-screen font-sans selection:bg-primary-500 selection:text-white text-gray-800">
        <!-- Header / Navigation -->
        <header class="bg-white/95 backdrop-blur-md sticky top-0 z-50 border-b border-gray-100 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <div class="flex items-center gap-2">
                        <UserGroupIcon class="w-8 h-8 md:w-10 md:h-10 text-primary-600" />
                        <span class="text-2xl md:text-3xl font-black text-secondary-900 tracking-tight">Siscoop<span class="text-primary-600">3F</span></span>
                    </div>

                    <!-- Desktop Menu -->
                    <nav class="hidden md:flex items-center gap-8">
                        <a href="#solucoes" class="text-sm font-semibold text-gray-700 hover:text-primary-600 transition">Sobre a Plataforma</a>
                        <a href="#modulos" class="text-sm font-semibold text-gray-700 hover:text-primary-600 transition">Módulos</a>
                        <a href="#faq" class="text-sm font-semibold text-gray-700 hover:text-primary-600 transition">Dúvidas Frequentes</a>
                        
                        <div v-if="canLogin" class="flex gap-4 items-center pl-4 border-l border-gray-200">
                            <Link v-if="$page.props.auth?.user" :href="route('master-admin.dashboard')" class="text-sm font-semibold text-gray-900 hover:text-primary-600 transition-colors duration-200">
                                Acessar Meu Painel
                            </Link>
                            <template v-else>
                                <Link :href="route('master-admin.login')" class="text-sm font-semibold text-gray-900 hover:text-primary-600 transition-colors duration-200 px-3 py-2">
                                    Acessar Conta
                                </Link>
                                <a href="#contato" class="rounded-full bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:focus:ring-2 hover:bg-primary-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                                    Agendar Demonstração
                                </a>
                            </template>
                        </div>
                    </nav>

                    <!-- Mobile Menu Button -->
                    <div class="md:hidden flex items-center">
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-700 hover:text-primary-600 p-2 transition-transform duration-200 active:scale-95">
                            <Bars3Icon v-if="!mobileMenuOpen" class="h-6 w-6" />
                            <XMarkIcon v-else class="h-6 w-6" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu Dropdown -->
            <div v-show="mobileMenuOpen" class="md:hidden bg-white border-b border-gray-100 px-4 pt-2 pb-6 space-y-2 shadow-lg">
                <a @click="mobileMenuOpen = false" href="#solucoes" class="block px-3 py-2 text-base font-semibold text-gray-700 rounded-md hover:bg-gray-50">Sobre a Plataforma</a>
                <a @click="mobileMenuOpen = false" href="#modulos" class="block px-3 py-2 text-base font-semibold text-gray-700 rounded-md hover:bg-gray-50">Módulos</a>
                <a @click="mobileMenuOpen = false" href="#faq" class="block px-3 py-2 text-base font-semibold text-gray-700 rounded-md hover:bg-gray-50">Dúvidas Frequentes</a>
                <div class="pt-4 mt-2 border-t border-gray-200 flex flex-col gap-3">
                    <Link v-if="canLogin && !$page.props.auth?.user" :href="route('master-admin.login')" class="block px-3 py-2 text-base font-semibold text-center text-gray-900 bg-gray-100 rounded-lg">Acessar Conta</Link>
                    <Link v-else-if="canLogin && $page.props.auth?.user" :href="route('master-admin.dashboard')" class="block px-3 py-2 text-base font-semibold text-center text-white bg-secondary-900 rounded-lg">Meu Painel</Link>
                    <a @click="mobileMenuOpen = false" href="#contato" class="block w-full rounded-lg bg-primary-600 px-5 py-3 text-center text-base font-semibold text-white shadow-sm">
                        Agendar Demonstração
                    </a>
                </div>
            </div>
        </header>

        <main>
            <!-- Hero Section -->
            <div class="relative isolate overflow-hidden">
                <!-- Background Pattern -->
                <div class="absolute inset-0 -z-10 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:16px_16px] opacity-40"></div>
                
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-16 pb-20 md:pt-24 md:pb-32 lg:flex lg:items-center lg:gap-x-10">
                    <div class="mx-auto max-w-2xl lg:mx-0 lg:flex-auto" v-reveal>
                        <div class="flex">
                            <span class="rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold leading-6 text-primary-700 ring-1 ring-inset ring-primary-600/20 mb-6">
                                Feito para o Cooperativismo
                            </span>
                        </div>
                        <h1 class="text-4xl sm:text-5xl md:text-6xl font-black tracking-tight text-secondary-900 leading-[1.1]">
                            Revolucione a gestão da sua cooperativa com o poder da <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-indigo-600">Inteligência Artificial</span>.
                        </h1>
                        <p class="mt-6 text-lg sm:text-xl leading-8 text-gray-600 max-w-lg">
                            Diga adeus à burocracia das planilhas e PDFs manuais. O Siscoop3F traz governança, agilidade e automação SaaS para o seu ramo, centralizando todas as operações em um único ambiente seguro.
                        </p>
                        <div class="mt-10 flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-6">
                            <a href="#contato" class="w-full sm:w-auto text-center rounded-full bg-primary-600 px-8 py-4 text-base font-bold text-white shadow-lg shadow-primary-500/30 hover:bg-primary-500 hover:scale-105 transition-all flex items-center justify-center gap-2">
                                Transformar minha Cooperativa
                                <ArrowRightIcon class="h-5 w-5" />
                            </a>
                        </div>
                        <p class="mt-4 text-sm text-gray-500 flex items-center gap-2">
                            <CheckCircleIcon class="h-4 w-4 text-green-500" /> Implantação ágil. Sem necessidade de servidores locais.
                        </p>
                    </div>
                    
                    <!-- Hero Image / Graphic -->
                    <div class="mt-16 sm:mt-24 lg:mt-0 lg:flex-shrink-0 lg:flex-grow flex justify-center">
                        <div class="relative mx-auto w-full max-w-md lg:max-w-xl" v-reveal="200">
                            <div class="relative rounded-2xl bg-white shadow-2xl ring-1 ring-gray-900/10 overflow-hidden">
                                <div class="bg-gray-800 px-4 py-3 flex items-center gap-2">
                                    <div class="h-3 w-3 rounded-full bg-red-400"></div>
                                    <div class="h-3 w-3 rounded-full bg-yellow-400"></div>
                                    <div class="h-3 w-3 rounded-full bg-green-400"></div>
                                </div>
                                <div class="p-4 sm:p-8 bg-gray-50 aspect-video flex items-center justify-center relative overflow-hidden">
                                     <!-- Mockup Visual de Dashboard Fictício -->
                                    <div class="w-full space-y-4 relative">
                                        <div class="flex gap-4">
                                            <div class="h-24 w-1/3 bg-white rounded-xl shadow-sm border border-gray-100 p-4 transition-transform duration-500 hover:-translate-y-2 hover:shadow-lg">
                                                <div class="h-3 w-16 bg-gray-200 rounded mb-3"></div>
                                                <div class="h-6 w-24 bg-primary-100 rounded"></div>
                                            </div>
                                            <div class="h-24 w-1/3 bg-white rounded-xl shadow-sm border border-gray-100 p-4 transition-transform duration-500 delay-75 hover:-translate-y-2 hover:shadow-lg">
                                                <div class="h-3 w-20 bg-gray-200 rounded mb-3"></div>
                                                <div class="h-6 w-20 bg-green-100 rounded"></div>
                                            </div>
                                            <div class="h-24 w-1/3 bg-white rounded-xl shadow-sm border border-gray-100 p-4 transition-transform duration-500 delay-150 hover:-translate-y-2 hover:shadow-lg">
                                                <div class="h-3 w-12 bg-gray-200 rounded mb-3"></div>
                                                <div class="h-6 w-16 bg-purple-100 rounded"></div>
                                            </div>
                                        </div>
                                        <div class="h-40 w-full bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-end gap-2 group cursor-pointer transition-all duration-300 hover:shadow-md">
                                            <div class="w-1/6 bg-indigo-100 rounded-t h-1/3 transition-all duration-500 group-hover:bg-indigo-200 group-hover:h-2/5"></div>
                                            <div class="w-1/6 bg-indigo-200 rounded-t h-2/3 transition-all duration-500 delay-75 group-hover:bg-indigo-300 group-hover:h-3/4"></div>
                                            <div class="w-1/6 bg-indigo-300 rounded-t h-full transition-all duration-500 delay-100 group-hover:bg-indigo-400 group-hover:h-[110%] relative -top-2"></div>
                                            <div class="w-1/6 bg-indigo-400 rounded-t h-4/5 transition-all duration-500 delay-150 group-hover:bg-indigo-500 group-hover:h-[90%]"></div>
                                            <div class="w-1/6 bg-indigo-500 rounded-t h-1/2 transition-all duration-500 delay-200 group-hover:bg-indigo-600 group-hover:h-3/5"></div>
                                            <div class="w-1/6 bg-indigo-600 rounded-t h-5/6 transition-all duration-500 delay-300 group-hover:bg-primary-500 group-hover:h-full"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Blur highlight -->
                                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-primary-400/30 rounded-full blur-3xl"></div>
                                    <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-indigo-400/30 rounded-full blur-3xl"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Problem vs Solution -->
            <div id="solucoes" class="bg-white py-24 sm:py-32">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center" v-reveal>
                        <h2 class="text-3xl font-bold tracking-tight text-secondary-900 sm:text-4xl">O fim do caos administrativo chegou.</h2>
                        <p class="mt-4 text-lg text-gray-600">Compare como as cooperativas operam hoje e como elas evoluem com o Siscoop3F.</p>
                    </div>

                    <div class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-8 lg:max-w-none lg:grid-cols-2">
                        <!-- O Passado -->
                        <div class="rounded-3xl bg-gray-50 p-8 sm:p-10 border border-gray-100 transition-all duration-300 hover:shadow-lg hover:-translate-y-1" v-reveal>
                            <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                                <span class="bg-red-100 text-red-600 p-1 rounded-md"><XMarkIcon class="w-5 h-5"/></span>
                                O Passado
                            </h3>
                            <ul class="space-y-6 text-base text-gray-600">
                                <li class="flex gap-4">
                                    <span class="text-xl">📉</span>
                                    <div>
                                        <strong class="text-gray-900 block">Tempo Perdido</strong>
                                        Horas gastas conciliando dezenas de planilhas de rateio e cotas no Excel.
                                    </div>
                                </li>
                                <li class="flex gap-4">
                                    <span class="text-xl">📄</span>
                                    <div>
                                        <strong class="text-gray-900 block">Burocracia</strong>
                                        Pilhas de fichas de adesão em papel, PDFs perdidos e guias médicas manuais.
                                    </div>
                                </li>
                                <li class="flex gap-4">
                                    <span class="text-xl">⚠️</span>
                                    <div>
                                        <strong class="text-gray-900 block">Risco e Ineficiência</strong>
                                        Falhas humanas em digitação, inconsistência de dados e processos engessados que impedem o crescimento da cooperativa.
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <!-- O Futuro -->
                        <div class="rounded-3xl bg-gradient-to-br from-primary-900 to-secondary-900 p-8 sm:p-10 shadow-xl ring-1 ring-white/10 transition-all duration-300 hover:shadow-2xl hover:shadow-primary-900/40 hover:-translate-y-1 relative overflow-hidden group" v-reveal="200">
                            <!-- Shine effect -->
                            <div class="absolute top-0 -inset-full h-full w-1/2 z-5 block transform -skew-x-12 bg-gradient-to-r from-transparent to-white opacity-10 group-hover:animate-[shine_1.5s]"></div>
                            
                            <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                                <span class="bg-green-500/20 text-green-400 p-1 rounded-md"><CheckCircleIcon class="w-5 h-5"/></span>
                                O Futuro com Siscoop3F
                            </h3>
                            <ul class="space-y-6 text-base text-gray-300">
                                <li class="flex gap-4">
                                    <span class="text-xl">🚀</span>
                                    <div>
                                        <strong class="text-white block">Automação Inteligente</strong>
                                        Nossa IA reduz a digitação manual a zero, lendo documentos e prevendo conciliações automaticamente.
                                    </div>
                                </li>
                                <li class="flex gap-4">
                                    <span class="text-xl">💻</span>
                                    <div>
                                        <strong class="text-white block">Fluxos 100% Digitais</strong>
                                        Adesão processada na web, faturamento automatizado e tudo hospedado na nuvem (SaaS Multi-tenancy puro).
                                    </div>
                                </li>
                                <li class="flex gap-4">
                                    <span class="text-xl">🔒</span>
                                    <div>
                                        <strong class="text-white block">Segurança e Conformidade</strong>
                                        Controle total para a diretoria com rastreabilidade completa e adequação total à LGPD.
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audience Beneficits (Tabs logic simulated with grid for mobile-first) -->
            <div id="beneficios" class="bg-secondary-50 py-24 sm:py-32">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl lg:text-center" v-reveal>
                        <h2 class="text-base font-semibold leading-7 text-primary-600">Uma plataforma, múltiplos papéis</h2>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-secondary-900 sm:text-4xl">
                            Feita para quem dirige, para quem opera e para quem coopera.
                        </p>
                    </div>

                    <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
                        <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-3">
                            <div v-for="(feature, index) in features" :key="feature.name" class="flex flex-col bg-white rounded-2xl p-8 shadow-sm border border-gray-100 transition-all duration-300 hover:shadow-xl hover:-translate-y-2 group cursor-default" v-reveal="index * 150">
                                <dt class="text-base font-semibold leading-7 text-secondary-900">
                                    <div class="mb-6 flex h-12 w-12 items-center justify-center rounded-xl bg-primary-100 transition-colors duration-300 group-hover:bg-primary-500">
                                        <component :is="feature.icon" class="h-6 w-6 text-primary-700 transition-colors duration-300 group-hover:text-white" aria-hidden="true" />
                                    </div>
                                    <span class="text-xs font-bold text-primary-600 uppercase tracking-wide block mb-1">{{ feature.audience }}</span>
                                    <span class="text-xl">{{ feature.name }}</span>
                                </dt>
                                <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-600">
                                    <p class="flex-auto">{{ feature.description }}</p>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Technical / Modules -->
            <div id="modulos" class="bg-secondary-900 py-24 sm:py-32 relative isolate overflow-hidden">
                <div class="absolute inset-0 -z-10 bg-[radial-gradient(#4f46e5_1px,transparent_1px)] [background-size:24px_24px] opacity-10"></div>
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl lg:text-center" v-reveal>
                        <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Tecnologia de ponta, feita sob medida para o Cooperativismo.</h2>
                        <p class="mt-4 text-lg leading-8 text-gray-300">O motor por trás da plataforma que escala junto com os seus objetivos.</p>
                    </div>
                    <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
                        <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-10 lg:max-w-none lg:grid-cols-3">
                            <div v-for="(tech, index) in technicals" :key="tech.name" class="relative pl-16 transition-all duration-300 hover:translate-x-2" v-reveal="index * 150">
                                <dt class="text-base font-semibold leading-7 text-white">
                                    <div class="absolute left-0 top-0 flex h-12 w-12 items-center justify-center rounded-xl bg-primary-500/10 border border-primary-500/20">
                                        <component :is="tech.icon" class="h-6 w-6 text-primary-400" aria-hidden="true" />
                                    </div>
                                    {{ tech.name }}
                                </dt>
                                <dd class="mt-2 text-base leading-7 text-gray-400">{{ tech.description }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Testimonials -->
            <div class="bg-white py-24 sm:py-32">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center" v-reveal>
                        <h2 class="text-3xl font-bold tracking-tight text-secondary-900 sm:text-4xl">Cooperativas que já aceleraram sua transformação digital conosco.</h2>
                    </div>
                    <div class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-8 lg:mx-0 lg:max-w-none lg:grid-cols-2">
                        <!-- Review 1 -->
                        <figure class="rounded-3xl bg-gray-50 p-8 sm:p-10 border border-gray-100 flex flex-col justify-between transition-all duration-300 hover:shadow-lg hover:-translate-y-1" v-reveal>
                            <blockquote class="text-lg leading-8 text-gray-700">
                                <p>"Antes do Siscoop3F, o fechamento do mês, o controle de cotas e guias dos cooperados era um pesadelo de planilhas separadas e papelada. Hoje, com a automação e a nuvem, nossa governança e rentabilidade deram um salto absurdo. É o sistema que faltava para a área médica."</p>
                            </blockquote>
                            <figcaption class="mt-6 flex items-center gap-x-4">
                                <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold text-xl">MV</div>
                                <div>
                                    <div class="font-semibold text-secondary-900">Dr. Marcelo Viana</div>
                                    <div class="text-sm text-gray-500">Diretor Administrativo de Coop. Saúde</div>
                                </div>
                            </figcaption>
                        </figure>
                        <!-- Review 2 -->
                        <figure class="rounded-3xl bg-gray-50 p-8 sm:p-10 border border-gray-100 flex flex-col justify-between transition-all duration-300 hover:shadow-lg hover:-translate-y-1" v-reveal="200">
                            <blockquote class="text-lg leading-8 text-gray-700">
                                <p>"A capacidade do sistema em isolar nossos dados e nos dar os módulos exatos que os agricultores precisavam foi fantástica. O portal do cooperado eliminou nossas filas de atendimento na safra. A adesão online mudou nosso jogo."</p>
                            </blockquote>
                            <figcaption class="mt-6 flex items-center gap-x-4">
                                <div class="h-12 w-12 rounded-full bg-green-200 flex items-center justify-center text-green-700 font-bold text-xl">CM</div>
                                <div>
                                    <div class="font-semibold text-secondary-900">Carla Mendes</div>
                                    <div class="text-sm text-gray-500">Gerente de Operações de Coop. Agrícola</div>
                                </div>
                            </figcaption>
                        </figure>
                    </div>
                </div>
            </div>

            <!-- FAQ Sections -->
            <div id="faq" class="bg-gray-50 py-24 sm:py-32">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl lg:text-center mb-16" v-reveal>
                        <h2 class="text-3xl font-bold tracking-tight text-secondary-900 sm:text-4xl">Dúvidas Frequentes</h2>
                    </div>
                    <div class="mx-auto max-w-3xl divide-y divide-gray-900/10">
                        <dl class="space-y-6 divide-y divide-gray-900/10">
                            <div v-for="(faq, index) in faqs" :key="faq.question" class="pt-6" v-reveal="index * 100">
                                <dt class="text-lg font-semibold leading-7 text-secondary-900">
                                    {{ faq.question }}
                                </dt>
                                <dd class="mt-2 text-base leading-7 text-gray-600">
                                    {{ faq.answer }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Contact/Lead Gen -->
            <div id="contato" class="bg-white py-24 sm:py-32">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="relative isolate overflow-hidden bg-secondary-900 px-6 py-24 shadow-2xl rounded-3xl sm:rounded-3xl sm:px-24" v-reveal>
                        <h2 class="mx-auto max-w-2xl text-center text-3xl font-bold tracking-tight text-white sm:text-4xl">Pronto para dar o próximo passo?</h2>
                        <p class="mx-auto mt-2 max-w-xl text-center text-lg leading-8 text-gray-300">
                            Fale com nossos especialistas, agende uma demonstração e descubra o que o Siscoop3F fará pela sua cooperativa.
                        </p>
                        
                        <form class="mx-auto mt-10 max-w-xl bg-white p-8 rounded-2xl shadow-lg">
                            <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label for="name" class="block text-sm font-semibold leading-6 text-gray-900">Nome Completo</label>
                                    <div class="mt-2">
                                        <input type="text" name="name" id="name" class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6" placeholder="João da Silva">
                                    </div>
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="company" class="block text-sm font-semibold leading-6 text-gray-900">Nome da Cooperativa</label>
                                    <div class="mt-2">
                                        <input type="text" name="company" id="company" class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6" placeholder="Cooperativa Exemplo">
                                    </div>
                                </div>
                                <div class="col-span-1">
                                    <label for="email" class="block text-sm font-semibold leading-6 text-gray-900">E-mail Corporativo</label>
                                    <div class="mt-2">
                                        <input type="email" name="email" id="email" class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6" placeholder="joao@cooperativa.com.br">
                                    </div>
                                </div>
                                <div class="col-span-1">
                                    <label for="phone" class="block text-sm font-semibold leading-6 text-gray-900">Telefone / WhatsApp</label>
                                    <div class="mt-2">
                                        <input type="tel" name="phone" id="phone" class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6" placeholder="(00) 00000-0000">
                                    </div>
                                </div>
                                <div class="col-span-1">
                                    <label for="ramo" class="block text-sm font-semibold leading-6 text-gray-900">Ramo da Cooperativa</label>
                                    <div class="mt-2">
                                        <select id="ramo" name="ramo" class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6 bg-white">
                                            <option>Saúde</option>
                                            <option>Agrícola</option>
                                            <option>Crédito</option>
                                            <option>Trabalho</option>
                                            <option>Consumo</option>
                                            <option>Outros</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-span-1">
                                    <label for="tamanho" class="block text-sm font-semibold leading-6 text-gray-900">Nº de Cooperados</label>
                                    <div class="mt-2">
                                        <select id="tamanho" name="tamanho" class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6 bg-white">
                                            <option>1 - 100</option>
                                            <option>101 - 500</option>
                                            <option>501 - 2000</option>
                                            <option>Mais de 2000</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-8">
                                <button type="button" class="block w-full rounded-md bg-primary-600 px-3.5 py-3 text-center text-sm font-semibold tracking-wide text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-xl active:scale-95">
                                    Agendar minha Demonstração Especializada
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer Global -->
        <footer class="bg-primary-900 border-t border-primary-800">
            <div class="mx-auto max-w-7xl overflow-hidden px-4 pb-12 pt-16 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                    <div class="col-span-1 md:col-span-2">
                        <span class="text-2xl font-black text-white tracking-tight block mb-4">Siscoop<span class="text-primary-300">3F</span></span>
                        <p class="text-sm text-primary-100 max-w-xs">
                            O Sistema de Gestão Inteligente focado na evolução e automação de cooperativas SaaS B2B.
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold leading-6 text-white uppercase tracking-wider mb-4">Plataforma</h3>
                        <ul role="list" class="space-y-3">
                            <li><a href="#solucoes" class="text-sm leading-6 text-primary-200 hover:text-white transition">Soluções</a></li>
                            <li><a href="#modulos" class="text-sm leading-6 text-primary-200 hover:text-white transition">Módulos</a></li>
                            <li><a href="#faq" class="text-sm leading-6 text-primary-200 hover:text-white transition">FAQ</a></li>
                            <li><Link :href="route('master-admin.login')" class="text-sm leading-6 text-primary-200 hover:text-white transition">Portal do Cooperado</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold leading-6 text-white uppercase tracking-wider mb-4">Legal</h3>
                        <ul role="list" class="space-y-3">
                            <li><a href="#" class="text-sm leading-6 text-primary-200 hover:text-white transition">Termos de Uso</a></li>
                            <li><a href="#" class="text-sm leading-6 text-primary-200 hover:text-white transition">Política de Privacidade</a></li>
                            <li><Link href="/lgpd" class="text-sm leading-6 text-primary-200 hover:text-white transition">Portal da Privacidade (LGPD)</Link></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-primary-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-center text-xs leading-5 text-primary-200">
                        &copy; 2026 Siscoop3F, Inc. Todos os direitos reservados.
                    </p>
                    <div class="flex justify-center space-x-6">
                        <!-- Redes Sociais Placeholder -->
                        <a href="#" class="text-primary-300 hover:text-primary-100">
                            <span class="sr-only">LinkedIn</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" clip-rule="evenodd" /></svg>
                        </a>
                        <a href="#" class="text-primary-300 hover:text-primary-100">
                            <span class="sr-only">Instagram</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
