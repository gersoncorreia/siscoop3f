<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    maxWidthClass: {
        type: String,
        default: 'sm:max-w-md'
    },
    fullWidth: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <div
        class="flex min-h-screen flex-col bg-[var(--bg-main)] transition-colors duration-500"
        :class="fullWidth ? '' : 'items-center pt-6 sm:justify-center sm:pt-0'"
    >
        <!-- Standard Layout (Centered Card) -->
        <template v-if="!fullWidth">
            <div class="mb-4">
                <slot name="logo" />
            </div>

            <div
                class="zynix-card mt-6 mb-12 w-full overflow-hidden px-8 py-10 shadow-2xl sm:rounded-2xl transition-all duration-500"
                :class="maxWidthClass"
            >
                <slot />
            </div>
        </template>

        <!-- Full Width Layout (for Split Screens) -->
        <template v-else>
            <slot />
        </template>
    </div>
</template>
