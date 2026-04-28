<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-full border-[var(--border-color)] bg-[var(--bg-card)] text-[var(--text-main)] shadow-sm focus:border-primary-dynamic focus:ring-primary-dynamic focus:ring-offset-0 transition-all placeholder:text-slate-400 px-6 py-3 text-sm"
        v-model="model"
        ref="input"
    />
</template>
