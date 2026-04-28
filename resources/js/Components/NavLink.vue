<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const page = usePage();
const primaryColor = computed(() => page.props.tenant?.primary_color || '#2563eb');
</script>

<template>
    <Link
        :href="href"
        :class="[
            active
                ? 'nav-link-active'
                : 'nav-link-inactive',
            'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-bold leading-5 focus:outline-none transition duration-150 ease-in-out h-16'
        ]"
    >
        <slot />
    </Link>
</template>

<style scoped>
.nav-link-active {
    border-color: v-bind(primaryColor);
    color: v-bind(primaryColor);
}
.nav-link-inactive {
    border-color: transparent;
    color: var(--text-muted);
}
.dark .nav-link-inactive {
    color: #8c90a7;
}
.nav-link-inactive:hover {
    color: v-bind(primaryColor);
    border-color: color-mix(in srgb, v-bind(primaryColor) 40%, transparent);
}
</style>
