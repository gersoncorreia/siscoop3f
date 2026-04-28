<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="href"
        class="block w-full px-4 py-2.5 text-start text-[13px] font-black text-[var(--text-main)] transition-all hover:bg-slate-100 dark:hover:bg-white/10 hover:pl-5 focus:outline-none"
    >
        <slot />
    </Link>
</template>
