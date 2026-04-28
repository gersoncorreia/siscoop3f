<script setup>
import { ref } from 'vue';
import { CloudArrowUpIcon, DocumentIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    modelValue: [File, null],
    id: String,
    label: {
        type: String,
        default: 'Escolher Arquivo'
    },
    accept: {
        type: String,
        default: '.pdf,.png,.jpg,.jpeg'
    }
});

const emit = defineEmits(['update:modelValue', 'change']);

const fileInput = ref(null);
const fileName = ref('');

function triggerInput() {
    fileInput.value.click();
}

function handleFileChange(e) {
    const file = e.target.files[0];
    if (file) {
        fileName.value = file.name;
        emit('update:modelValue', file);
        emit('change', e);
    }
}
</script>

<template>
    <div class="relative">
        <input
            type="file"
            :id="id"
            ref="fileInput"
            class="hidden"
            :accept="accept"
            @change="handleFileChange"
        />
        <div 
            @click="triggerInput"
            class="flex items-center gap-3 w-full p-4 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl bg-slate-50 dark:bg-slate-900/50 hover:bg-primary-50 dark:hover:bg-primary-500/5 hover:border-primary-dynamic transition-all cursor-pointer group"
        >
            <div class="p-2 rounded-xl bg-white dark:bg-slate-800 text-slate-400 group-hover:text-primary-dynamic shadow-sm transition-colors">
                <CloudArrowUpIcon class="w-5 h-5" />
            </div>
            <div class="flex-1 overflow-hidden">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 group-hover:text-primary-dynamic truncate">
                    {{ fileName || label }}
                </p>
                <p v-if="fileName" class="text-[8px] text-slate-400 font-bold uppercase tracking-tighter mt-0.5">Clique para trocar o arquivo</p>
                <p v-else class="text-[8px] text-slate-400 font-bold uppercase tracking-tighter mt-0.5">Suporta PDF, JPG e PNG (Máx 10MB)</p>
            </div>
            <div v-if="fileName" class="text-emerald-500 animate-bounce">
                <DocumentIcon class="w-4 h-4" />
            </div>
        </div>
    </div>
</template>
