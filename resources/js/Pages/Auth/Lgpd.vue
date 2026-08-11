<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    submitRoute: {
        type: String,
        default: 'lgpd.accept',
    },
    terms: {
        type: Object,
        required: true,
    }
});

const form = useForm({});

const submit = () => {
    form.post(route(props.submitRoute));
};
</script>

<template>
    <Head :title="terms.titulo" />

    <GuestLayout>
        <div class="mb-4 text-sm text-gray-600">
            <h2 class="text-xl font-bold text-gray-900 mb-4">{{ terms.titulo }}</h2>
            <p class="mb-4">{{ terms.introducao }}</p>
            
            <div class="h-48 overflow-y-auto p-4 bg-gray-50 border rounded-md text-xs text-gray-700 leading-relaxed mb-6 space-y-4">
                <div v-for="(section, idx) in terms.secoes" :key="idx">
                    <p class="font-bold mb-1">{{ section.titulo }}</p>
                    <p>{{ section.conteudo }}</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit">
            <div class="flex items-center justify-end mt-4">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ terms.botao_texto }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
