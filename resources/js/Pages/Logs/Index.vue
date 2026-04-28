<script setup>
import { computed, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { 
  ShieldCheckIcon,
  FunnelIcon,
  TrashIcon,
  ArrowPathIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  InformationCircleIcon,
  XCircleIcon,
  CommandLineIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  logs: Array,
  secret: String,
})

const filter = ref('')
const levelFilter = ref('ALL')

const filteredLogs = computed(() => {
  return props.logs.filter(log => {
    const matchesText = log.message.toLowerCase().includes(filter.value.toLowerCase()) || 
                       log.date.includes(filter.value)
    const matchesLevel = levelFilter.value === 'ALL' || log.level === levelFilter.value
    return matchesText && matchesLevel
  })
})

const getLevelClass = (level) => {
  switch (level) {
    case 'ERROR':
    case 'CRITICAL':
    case 'ALERT':
    case 'EMERGENCY':
      return 'bg-red-500/10 text-red-500 border-red-500/20'
    case 'WARNING':
      return 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20'
    case 'INFO':
      return 'bg-blue-500/10 text-blue-500 border-blue-500/20'
    default:
      return 'bg-gray-500/10 text-gray-400 border-gray-500/20'
  }
}

const getLevelIcon = (level) => {
  switch (level) {
    case 'ERROR':
    case 'CRITICAL':
      return XCircleIcon
    case 'WARNING':
      return ExclamationCircleIcon
    case 'INFO':
      return InformationCircleIcon
    default:
      return CheckCircleIcon
  }
}

const refreshLogs = () => {
  window.location.reload()
}
</script>

<template>
  <Head title="Zynix Log Explorer" />

  <div class="min-h-screen bg-[var(--bg-main)] text-[var(--text-main)] font-sans antialiased p-6">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div class="flex items-center gap-4">
          <div class="p-3 rounded-2xl bg-primary-600/10 border border-primary-600/20 shadow-lg shadow-primary-600/5">
            <CommandLineIcon class="w-8 h-8 text-primary-500" />
          </div>
          <div>
            <h1 class="text-2xl font-black tracking-tight flex items-center gap-3">
              Zynix Log Explorer
              <span class="px-2 py-0.5 text-[10px] font-bold bg-primary-600 text-white rounded-md uppercase tracking-wider">Internal</span>
            </h1>
            <p class="text-sm text-[var(--text-muted)] font-medium">Monitoramento em tempo real do ecossistema Siscoop3F</p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <button 
            @click="refreshLogs"
            class="p-3 rounded-xl bg-[var(--bg-card)] border border-[var(--bg-header)] hover:bg-[var(--bg-header)] transition-all active:scale-95 shadow-sm text-[var(--text-muted)]"
            title="Atualizar Logs"
          >
            <ArrowPathIcon class="w-5 h-5" />
          </button>
          <div class="h-10 w-[1px] bg-[var(--bg-header)] mx-2"></div>
          <div class="flex items-center gap-2 px-4 py-2 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
            <ShieldCheckIcon class="w-4 h-4 text-emerald-500" />
            <span class="text-xs font-bold text-emerald-500 uppercase tracking-widest">Secret Mode Active</span>
          </div>
        </div>
      </div>

      <!-- Filters & Stats -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
        <div class="lg:col-span-2 relative">
          <FunnelIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--text-muted)]" />
          <input 
            v-model="filter"
            type="text" 
            placeholder="Filtrar por mensagem, data ou contexto..." 
            class="w-full bg-[var(--bg-card)] border border-[var(--bg-header)] rounded-2xl pl-12 pr-4 py-3.5 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-all placeholder:text-[var(--text-muted)]/50 font-medium"
          />
        </div>
        
        <div class="relative">
          <select 
            v-model="levelFilter"
            class="w-full bg-[var(--bg-card)] border border-[var(--bg-header)] rounded-2xl px-4 py-3.5 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-all font-bold appearance-none cursor-pointer"
          >
            <option value="ALL">TODOS OS NÍVEIS</option>
            <option value="ERROR">ERROS</option>
            <option value="CRITICAL">CRÍTICOS</option>
            <option value="WARNING">AVISOS</option>
            <option value="INFO">INFORMAÇÕES</option>
          </select>
          <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none">
            <FunnelIcon class="w-4 h-4 text-[var(--text-muted)]" />
          </div>
        </div>

        <div class="bg-[var(--bg-card)] border border-[var(--bg-header)] rounded-2xl px-6 py-3.5 flex items-center justify-between">
          <span class="text-xs font-bold text-[var(--text-muted)] uppercase tracking-wider">Total Visível</span>
          <span class="text-lg font-black text-primary-500">{{ filteredLogs.length }}</span>
        </div>
      </div>

      <!-- Log Table -->
      <div class="bg-[var(--bg-card)] border border-[var(--bg-header)] rounded-3xl overflow-hidden shadow-xl shadow-black/10">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-[var(--bg-header)] text-[10px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em]">
                <th class="px-6 py-4 w-48">Timestamp</th>
                <th class="px-6 py-4 w-32 text-center">Nível</th>
                <th class="px-6 py-4">Mensagem / Contexto</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[var(--bg-header)]/50">
              <tr v-if="filteredLogs.length === 0">
                <td colspan="3" class="px-6 py-20 text-center">
                  <InformationCircleIcon class="w-12 h-12 text-[var(--text-muted)]/20 mx-auto mb-4" />
                  <p class="text-[var(--text-muted)] font-medium">Nenhum log encontrado para os filtros selecionados.</p>
                </td>
              </tr>
              <tr 
                v-for="(log, index) in filteredLogs" 
                :key="index"
                class="hover:bg-primary-500/5 transition-colors group"
              >
                <td class="px-6 py-4 text-[11px] font-mono text-[var(--text-muted)] whitespace-nowrap">
                  {{ log.date }}
                </td>
                <td class="px-6 py-4">
                  <div class="flex justify-center">
                    <span 
                      :class="getLevelClass(log.level)"
                      class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border"
                    >
                      <component :is="getLevelIcon(log.level)" class="w-3.5 h-3.5" />
                      {{ log.level }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex flex-col gap-1">
                    <p class="text-sm font-bold text-[var(--text-main)] group-hover:text-primary-500 transition-colors leading-snug">
                      {{ log.message }}
                    </p>
                    <!-- Highlight context if present in message (simplified) -->
                    <div v-if="log.message.includes('{')" class="bg-black/20 rounded-xl p-3 mt-2 font-mono text-[11px] text-emerald-400 overflow-x-auto">
                      {{ log.message.substring(log.message.indexOf('{')) }}
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Footer -->
      <div class="mt-8 text-center">
        <p class="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-[0.3em]">
          Zynix Cloud Systems &copy; 2026 - Powered by Siscoop3F Core
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Scrollbar custom logic */
::-webkit-scrollbar {
  width: 10px;
  height: 10px;
}
::-webkit-scrollbar-track {
  background: var(--bg-main);
}
::-webkit-scrollbar-thumb {
  background: var(--bg-header);
  border-radius: 5px;
  border: 3px solid var(--bg-main);
}
::-webkit-scrollbar-thumb:hover {
  background: var(--text-muted);
}
</style>
