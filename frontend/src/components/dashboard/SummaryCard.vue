<template>
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

    <!-- Saldo -->
    <div class="relative overflow-hidden bg-gradient-to-br from-indigo-500 to-violet-600 rounded-2xl p-5 text-white shadow-lg shadow-indigo-200">
      <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
      <div class="absolute -bottom-6 -right-2 w-32 h-32 bg-white/5 rounded-full"></div>
      <div class="relative">
        <div class="flex items-center gap-2 mb-3">
          <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center text-sm">💰</div>
          <p class="text-sm font-medium text-indigo-100">Total Saldo</p>
        </div>
        <p class="text-2xl font-bold tracking-tight">{{ formatRupiah(summary.balance) }}</p>
        <p class="text-xs text-indigo-200 mt-1">
          {{ parseFloat(summary.balance) >= 0 ? '▲ Keuangan sehat' : '▼ Perlu perhatian' }}
        </p>
      </div>
    </div>

    <!-- Pemasukan -->
    <div class="relative overflow-hidden bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
      <div class="absolute top-0 right-0 w-20 h-20 bg-green-50 rounded-bl-full opacity-60"></div>
      <div class="relative">
        <div class="flex items-center gap-2 mb-3">
          <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center text-sm">📈</div>
          <p class="text-sm font-medium text-gray-500">Total Pemasukan</p>
        </div>
        <p class="text-2xl font-bold text-green-600 tracking-tight">{{ formatRupiah(summary.total_income) }}</p>
        <div class="flex items-center gap-1 mt-1">
          <span class="text-xs text-green-500 font-medium">+</span>
          <span class="text-xs text-gray-400">Semua waktu</span>
        </div>
      </div>
    </div>

    <!-- Pengeluaran -->
    <div class="relative overflow-hidden bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
      <div class="absolute top-0 right-0 w-20 h-20 bg-red-50 rounded-bl-full opacity-60"></div>
      <div class="relative">
        <div class="flex items-center gap-2 mb-3">
          <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center text-sm">📉</div>
          <p class="text-sm font-medium text-gray-500">Total Pengeluaran</p>
        </div>
        <p class="text-2xl font-bold text-red-500 tracking-tight">{{ formatRupiah(summary.total_expense) }}</p>
        <div class="flex items-center gap-1 mt-1">
          <span class="text-xs text-red-400 font-medium">-</span>
          <span class="text-xs text-gray-400">Semua waktu</span>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
defineProps({
  summary: {
    type: Object,
    default: () => ({ total_income: '0.00', total_expense: '0.00', balance: '0.00' }),
  },
})

function formatRupiah(value) {
  const num = parseFloat(value) || 0
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num)
}
</script>
