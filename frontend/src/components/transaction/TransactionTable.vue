<template>
  <!-- Empty state -->
  <div v-if="transactions.length === 0" class="flex flex-col items-center justify-center py-16 gap-4">
    <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center text-4xl">🧾</div>
    <div class="text-center">
      <p class="font-semibold text-gray-700">Tidak ada transaksi ditemukan</p>
      <p class="text-sm text-gray-400 mt-1">Coba ubah filter atau tambahkan transaksi baru</p>
    </div>
  </div>

  <template v-else>
    <!-- ── DESKTOP: Table (md ke atas) ─────────────────────────────────────── -->
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-gray-50/80 border-b border-gray-100">
            <th class="text-left px-5 py-3.5 font-semibold text-gray-500 text-xs uppercase tracking-wide">Transaksi</th>
            <th class="text-left px-5 py-3.5 font-semibold text-gray-500 text-xs uppercase tracking-wide">Kategori</th>
            <th class="text-left px-5 py-3.5 font-semibold text-gray-500 text-xs uppercase tracking-wide">Tanggal</th>
            <th class="text-right px-5 py-3.5 font-semibold text-gray-500 text-xs uppercase tracking-wide">Nominal</th>
            <th class="w-20"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr
            v-for="trx in transactions"
            :key="trx.id"
            :class="[
              'group transition-colors',
              isLargest(trx) ? 'bg-amber-50/40 hover:bg-amber-50/70' : 'hover:bg-gray-50/80',
            ]"
          >
            <!-- Judul -->
            <td class="px-5 py-3.5">
              <div class="flex items-center gap-3">
                <div
                  :class="[
                    'w-9 h-9 rounded-xl flex items-center justify-center text-sm flex-shrink-0',
                    trx.type === 'income' ? 'bg-green-100' : 'bg-red-100',
                  ]"
                >
                  {{ trx.type === 'income' ? '📈' : '📉' }}
                </div>
                <div class="min-w-0">
                  <div class="flex items-center gap-2">
                    <p class="font-medium text-gray-900 leading-tight truncate max-w-[200px]">{{ trx.title }}</p>
                    <span v-if="isLargest(trx)" class="text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-md font-medium flex-shrink-0">
                      Terbesar
                    </span>
                  </div>
                  <p v-if="trx.notes" class="text-xs text-gray-400 mt-0.5 truncate max-w-[200px]">{{ trx.notes }}</p>
                </div>
              </div>
            </td>

            <!-- Kategori -->
            <td class="px-5 py-3.5">
              <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-600">
                {{ trx.category?.name ?? '-' }}
              </span>
            </td>

            <!-- Tanggal -->
            <td class="px-5 py-3.5 text-gray-500 text-sm">
              {{ formatDate(trx.transaction_date) }}
            </td>

            <!-- Nominal -->
            <td class="px-5 py-3.5 text-right">
              <span
                :class="[
                  'font-semibold text-sm',
                  trx.type === 'income' ? 'text-green-600' : 'text-red-500',
                ]"
              >
                {{ trx.type === 'income' ? '+' : '-' }}{{ formatRupiah(trx.amount) }}
              </span>
            </td>

            <!-- Aksi -->
            <td class="px-5 py-3.5">
              <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                <button
                  @click="$emit('edit', trx)"
                  class="w-8 h-8 flex items-center justify-center rounded-lg text-indigo-500 hover:bg-indigo-50 hover:text-indigo-700 transition-colors"
                  title="Edit"
                >✏️</button>
                <button
                  @click="$emit('delete', trx.id)"
                  class="w-8 h-8 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors"
                  title="Hapus"
                >🗑️</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ── MOBILE: Card list (di bawah md) ─────────────────────────────────── -->
    <div class="md:hidden divide-y divide-gray-50">
      <div
        v-for="trx in transactions"
        :key="trx.id"
        :class="[
          'px-4 py-4 transition-colors active:bg-gray-50',
          isLargest(trx) ? 'bg-amber-50/40' : '',
        ]"
      >
        <div class="flex items-start gap-3">
          <!-- Icon -->
          <div
            :class="[
              'w-10 h-10 rounded-xl flex items-center justify-center text-base flex-shrink-0 mt-0.5',
              trx.type === 'income' ? 'bg-green-100' : 'bg-red-100',
            ]"
          >
            {{ trx.type === 'income' ? '📈' : '📉' }}
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0">
                <div class="flex items-center gap-1.5 flex-wrap">
                  <p class="font-semibold text-gray-900 text-sm leading-tight">{{ trx.title }}</p>
                  <span v-if="isLargest(trx)" class="text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-md font-medium">
                    Terbesar
                  </span>
                </div>
                <div class="flex items-center gap-2 mt-1 flex-wrap">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-600">
                    {{ trx.category?.name ?? '-' }}
                  </span>
                  <span class="text-xs text-gray-400">{{ formatDate(trx.transaction_date) }}</span>
                </div>
                <p v-if="trx.notes" class="text-xs text-gray-400 mt-1 truncate">{{ trx.notes }}</p>
              </div>

              <!-- Nominal + actions -->
              <div class="flex flex-col items-end gap-2 flex-shrink-0">
                <span
                  :class="[
                    'font-bold text-sm',
                    trx.type === 'income' ? 'text-green-600' : 'text-red-500',
                  ]"
                >
                  {{ trx.type === 'income' ? '+' : '-' }}{{ formatRupiah(trx.amount) }}
                </span>
                <div class="flex items-center gap-1">
                  <button
                    @click="$emit('edit', trx)"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-indigo-500 hover:bg-indigo-50 transition-colors text-sm"
                  >✏️</button>
                  <button
                    @click="$emit('delete', trx.id)"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 transition-colors text-sm"
                  >🗑️</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  transactions: { type: Array, default: () => [] },
})
defineEmits(['edit', 'delete'])

// Highlight transaksi dengan nominal terbesar (expense)
const largestExpenseId = computed(() => {
  const expenses = props.transactions.filter(t => t.type === 'expense')
  if (!expenses.length) return null
  return expenses.reduce((max, t) =>
    parseFloat(t.amount) > parseFloat(max.amount) ? t : max
  ).id
})

function isLargest(trx) {
  return trx.type === 'expense' && trx.id === largestExpenseId.value
}

function formatRupiah(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', minimumFractionDigits: 0,
  }).format(parseFloat(value) || 0)
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric',
  })
}
</script>
