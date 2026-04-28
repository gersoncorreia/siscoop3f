<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { nextTick, ref, computed } from 'vue';

const page = usePage();
const isCooperado = computed(() => page.props.isCooperado);

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const deleteRoute = computed(() => {
    try {
        return isCooperado.value ? route('cooperado.perfil.destroy') : route('profile.destroy');
    } catch {
        return '/cooperado/perfil';
    }
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(deleteRoute.value, {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-xl font-black text-rose-600 dark:text-rose-400 tracking-tighter">
                Excluir Conta
            </h2>

            <p class="mt-1 text-sm font-bold text-[var(--text-muted)] uppercase tracking-widest text-[10px] opacity-60">
                Depois que sua conta for excluída, todos os seus recursos e dados serão excluídos permanentemente.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">Excluir Conta Permanentemente</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal" max-width="md">
            <div class="p-8">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-500 flex items-center justify-center shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-[var(--text-heading)] tracking-tighter">Confirmar Exclusão</h2>
                        <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest mt-1">Ação Irreversível</p>
                    </div>
                </div>

                <p class="text-sm font-medium text-[var(--text-muted)] leading-relaxed">
                    Depois que sua conta for excluída, todos os seus recursos e dados serão excluídos permanentemente. Por favor, digite sua senha para confirmar.
                </p>

                <div class="mt-8">
                    <InputLabel
                        for="password"
                        value="Senha de Confirmação"
                        class="sr-only"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="block w-full bg-[var(--bg-main)]"
                        placeholder="Sua senha atual"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-10 flex justify-end gap-3">
                    <SecondaryButton @click="closeModal" class="!rounded-full !py-4 !px-8">
                        Cancelar
                    </SecondaryButton>

                    <DangerButton
                        class="!rounded-full !py-4 !px-8 shadow-xl shadow-rose-500/20"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Confirmar Exclusão
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
