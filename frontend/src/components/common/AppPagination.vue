<template>
  <div v-if="meta && meta.last_page > 1" class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-5 px-1">
    <!-- Info -->
    <p class="text-sm text-gray-500">
      Menampilkan
      <span class="font-semibold text-gray-700">{{ meta.from ?? 0 }}–{{ meta.to ?? 0 }}</span>
      dari
      <span class="font-semibold text-gray-700">{{ meta.total }}</span>
      transaksi
    </p>

    <!-- Buttons -->
    <div class="flex items-center gap-1">
      <button
        @click="$emit('page-change', meta.current_page - 1)"
        :disabled="meta.current_page === 1 || disabled"
        class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-50 hover:border-gray-300 disabled:opacity-40 disabled:cursor-not-allowed transition-all text-sm"
      >←</button>

      <template v-for="page in visiblePages" :key="page">
        <span v-if="page === '...'" class="w-9 h-9 flex items-center justify-center text-gray-400 text-sm">…</span>
        <button
          v-else
          @click="$emit('page-change', page)"
          :disabled="disabled"
          :class="[
            'w-9 h-9 flex items-center justify-center rounded-xl text-sm font-medium transition-all',
            page === meta.current_page
              ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200'
              : 'border border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300',
          ]"
        >{{ page }}</button>
      </template>

      <button
        @click="$emit('page-change', meta.current_page + 1)"
        :disabled="meta.current_page === meta.last_page || disabled"
        class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-50 hover:border-gray-300 disabled:opacity-40 disabled:cursor-not-allowed transition-all text-sm"
      >→</button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  meta:     { type: Object, default: null },
  disabled: { type: Boolean, default: false },
})
defineEmits(['page-change'])

const visiblePages = computed(() => {
  if (!props.meta) return []
  const { current_page, last_page } = props.meta
  if (last_page <= 7) return Array.from({ length: last_page }, (_, i) => i + 1)
  const pages = [1]
  if (current_page > 3) pages.push('...')
  for (let i = Math.max(2, current_page - 1); i <= Math.min(last_page - 1, current_page + 1); i++) pages.push(i)
  if (current_page < last_page - 2) pages.push('...')
  pages.push(last_page)
  return pages
})
</script>
