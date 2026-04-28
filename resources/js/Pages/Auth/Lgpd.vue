<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    submitRoute: {
        type: String,
        default: 'lgpd.accept',
    }
});

const form = useForm({});

const submit = () => {
    form.post(route(props.submitRoute));
};
</script>

<template>
    <Head title="Termos de Uso e LGPD" />

    <GuestLayout>
        <div class="mb-4 text-sm text-gray-600">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Termos de Uso e Privacidade (LGPD)</h2>
            <p class="mb-4">
                Para continuar acessando o Siscoop3F e garantir sua segurança, solicitamos que você leia e concorde com nossa política de privacidade em adequação à Lei Geral de Proteção de Dados (LGPD).
            </p>
            <div class="h-48 overflow-y-auto p-4 bg-gray-50 border rounded-md text-xs text-gray-700 leading-relaxed mb-6">
                <p class="font-bold mb-2">1. Coleta de Dados</p>
                <p class="mb-4">O Siscoop3F coleta informações pessoais estritamente necessárias para o gerenciamento das atividades cooperativas, como identificação, dados de contato e financeiros das cotas de capital.</p>

                <p class="font-bold mb-2">2. Uso e Compartilhamento</p>
                <p class="mb-4">Os dados coletados serão usados exclusivamente pela sua cooperativa para fins administrativos e de prestação de serviços não sendo compartilhados com terceiros sem consentimento explícito, salvo obrigações legais.</p>

                <p class="font-bold mb-2">3. Seus Direitos</p>
                <p>O associado possui o direito de exigir a alteração e anonimização de dados caso retire-se da organização.</p>
            </div>
        </div>

        <form @submit.prevent="submit">
            <div class="flex items-center justify-end mt-4">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Eu Li e Concordo com os Termos
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
