<template>
  <div>
    <!-- Loading -->
    <div v-if="isLoading" class="divide-y divide-gray-50">
      <div v-for="i in 5" :key="i" class="flex items-center gap-4 px-6 py-4">
        <div class="w-9 h-9 bg-gray-200 rounded-xl animate-pulse flex-shrink-0"></div>
        <div class="flex-1 space-y-2">
          <div class="h-3 bg-gray-200 rounded animate-pulse w-1/3"></div>
          <div class="h-2.5 bg-gray-100 rounded animate-pulse w-1/5"></div>
        </div>
        <div class="h-4 bg-gray-200 rounded animate-pulse w-20"></div>
      </div>
    </div>

    <!-- Empty -->
    <div v-else-if="transactions.length === 0" class="flex flex-col items-center justify-center py-12 gap-3">
      <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center text-2xl">🧾</div>
      <p class="text-sm text-gray-500">Belum ada transaksi</p>
    </div>

    <!-- List -->
    <div v-else class="divide-y divide-gray-50">
      <div
        v-for="trx in transactions"
        :key="trx.id"
        class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50/60 transition-colors"
      >
        <!-- Icon -->
        <div
          :class="[
            'w-9 h-9 rounded-xl flex items-center justify-center text-sm flex-shrink-0',
            trx.type === 'income' ? 'bg-green-100' : 'bg-red-100',
          ]"
        >
          {{ trx.type === 'income' ? '📈' : '📉' }}
        </div>

        <!-- Info -->
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-900 truncate">{{ trx.title }}</p>
          <div class="flex items-center gap-2 mt-0.5">
            <span class="text-xs text-gray-400">{{ trx.category?.name ?? '-' }}</span>
            <span class="text-gray-300 text-xs">·</span>
            <span class="text-xs text-gray-400">{{ formatDate(trx.transaction_date) }}</span>
          </div>
        </div>

        <!-- Amount -->
        <span
          :class="[
            'text-sm font-semibold flex-shrink-0',
            trx.type === 'income' ? 'text-green-600' : 'text-red-500',
          ]"
        >
          {{ trx.type === 'income' ? '+' : '-' }}{{ formatRupiah(trx.amount) }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const transactions = ref([])
const isLoading    = ref(false)

onMounted(async () => {
  isLoading.value = true
  try {
    const { data } = await api.get('/transactions', {
      params: { per_page: 5, sort_by: 'transaction_date', sort_order: 'desc' },
    })
    transactions.value = data.data?.data ?? data.data ?? []
  } finally {
    isLoading.value = false
  }
})

function formatRupiah(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', minimumFractionDigits: 0,
  }).format(parseFloat(value) || 0)
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })
}
</script>
