<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CooperadoLayout from '@/Layouts/CooperadoLayout.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const isCooperado = computed(() => page.props.isCooperado);
const Layout = computed(() => isCooperado.value ? CooperadoLayout : AuthenticatedLayout);

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <component :is="Layout">
        <template #header>
            <h2
                class="text-2xl font-black text-[var(--text-heading)] tracking-tighter"
            >
                Configurações da Conta
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                
                <div v-if="page.props.flash?.error" class="bg-rose-500/10 border border-rose-500/20 text-rose-500 p-6 rounded-2xl mb-6 shadow-sm flex items-start gap-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 mt-0.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <h4 class="font-black text-sm uppercase tracking-widest">Atenção! Cadastro Incompleto</h4>
                        <p class="text-sm font-medium mt-1 opacity-90">{{ page.props.flash.error }}</p>
                    </div>
                </div>

                <div
                    class="zynix-card !p-8 sm:!p-12 !rounded-[2rem]"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div
                    class="zynix-card !p-8 sm:!p-12 !rounded-[2rem]"
                >
                    <UpdatePasswordForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </component>
</template>
