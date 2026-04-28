<script setup>
import { Link } from '@inertiajs/vue3';
import { ChevronRightIcon, HomeIcon } from '@heroicons/vue/20/solid';

defineProps({
    items: {
        type: Array,
        default: () => [],
        // Array of { label: string, href?: string }
    }
});
</script>

<template>
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol role="list" class="flex items-center space-x-2">
            <li>
                <div>
                    <Link href="/dashboard" class="text-slate-400 hover:text-slate-500 transition-colors">
                        <HomeIcon class="h-4 w-4 flex-shrink-0" aria-hidden="true" />
                        <span class="sr-only">Dashboard</span>
                    </Link>
                </div>
            </li>
            <li v-for="(item, index) in items" :key="index">
                <div class="flex items-center">
                    <ChevronRightIcon class="h-4 w-4 flex-shrink-0 text-slate-300" aria-hidden="true" />
                    <Link 
                        v-if="item.href" 
                        :href="item.href" 
                        class="ml-2 text-xs font-bold text-slate-400 hover:text-slate-600 transition-colors uppercase tracking-widest"
                    >
                        {{ item.label }}
                    </Link>
                    <span 
                        v-else 
                        class="ml-2 text-xs font-black text-slate-600 dark:text-slate-200 uppercase tracking-widest"
                    >
                        {{ item.label }}
                    </span>
                </div>
            </li>
        </ol>
    </nav>
</template>
