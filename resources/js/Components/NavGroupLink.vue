<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    href: String,
    active: Boolean,
});

const page = usePage();
const primaryColor = computed(() => page.props.tenant?.primary_color || '#2563eb');
</script>

<template>
    <Link
        :href="href"
        :class="[
            active ? 'nav-sublink-active' : 'nav-sublink-inactive',
            'block px-4 py-3 text-sm transition-all duration-200 flex items-center group'
        ]"
    >
        <div v-if="active" class="nav-sublink-indicator w-1 h-4 rounded-full mr-3"></div>
        <slot />
    </Link>
</template>

<style scoped>
.nav-sublink-active {
    background-color: color-mix(in srgb, v-bind(primaryColor) 8%, white);
    color: v-bind(primaryColor);
    font-weight: 700;
}
.nav-sublink-indicator {
    background-color: v-bind(primaryColor);
}
.nav-sublink-inactive {
    color: #475569;
}
.nav-sublink-inactive:hover {
    background-color: color-mix(in srgb, v-bind(primaryColor) 6%, white);
    color: v-bind(primaryColor);
}
</style>
