<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { ChevronDownIcon } from '@heroicons/vue/20/solid';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    title: String,
    active: Boolean,
});

const open = ref(false);
const page = usePage();
const primaryColor = computed(() => page.props.tenant?.primary_color || '#2563eb');

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));
</script>

<template>
    <div class="relative">
        <div>
            <button
                @click="open = !open"
                :class="[
                    active 
                        ? 'nav-group-active' 
                        : 'nav-group-inactive',
                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out h-16'
                ]"
            >
                <slot name="icon" />
                {{ title }}
                <ChevronDownIcon :class="{'rotate-180': open}" class="ml-1 h-4 w-4 transition-transform duration-200" />
            </button>
        </div>

        <!-- Dropdown Menu -->
        <div v-show="open" class="fixed inset-0 z-40" @click="open = false"></div>

        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-show="open"
                class="absolute z-50 mt-2 w-48 rounded-2xl shadow-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 ring-1 ring-black ring-opacity-5 py-2 focus:outline-none overflow-hidden"
                @click="open = false"
            >
                <div class="px-3 py-2 border-b border-slate-50 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-800/50">
                    <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ title }}</span>
                </div>
                <slot />
            </div>
        </transition>
    </div>
</template>

<style scoped>
.nav-group-active {
    border-color: v-bind(primaryColor);
    color: v-bind(primaryColor);
}
.nav-group-inactive {
    border-color: transparent;
    color: var(--text-muted);
}
.dark .nav-group-inactive {
    color: #8c90a7;
}
.nav-group-inactive:hover {
    color: v-bind(primaryColor);
    border-color: color-mix(in srgb, v-bind(primaryColor) 40%, transparent);
}
</style>
