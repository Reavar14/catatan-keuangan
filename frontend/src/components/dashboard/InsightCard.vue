<template>
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

    <!-- Pengeluaran bulan ini vs bulan lalu -->
    <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md transition-shadow">
      <div class="flex items-start justify-between mb-3">
        <div class="w-9 h-9 bg-orange-100 rounded-xl flex items-center justify-center text-lg">📊</div>
        <span
          v-if="expenseChange !== null"
          :class="[
            'inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-lg',
            expenseChange > 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700',
          ]"
        >
          {{ expenseChange > 0 ? '↑' : '↓' }} {{ Math.abs(expenseChange) }}%
        </span>
      </div>
      <p class="text-xs text-gray-400 mb-1">Pengeluaran Bulan Ini</p>
      <p class="text-xl font-bold text-gray-900">{{ formatRupiah(currentMonth?.expense ?? 0) }}</p>
      <p class="text-xs mt-1.5" :class="expenseChange > 0 ? 'text-red-500' : 'text-green-500'">
        <template v-if="expenseChange > 0">
          Naik {{ Math.abs(expenseChange) }}% dari bulan lalu
        </template>
        <template v-else-if="expenseChange < 0">
          Turun {{ Math.abs(expenseChange) }}% dari bulan lalu
        </template>
        <template v-else>
          Sama dengan bulan lalu
        </template>
      </p>
    </div>

    <!-- Pemasukan bulan ini vs bulan lalu -->
    <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md transition-shadow">
      <div class="flex items-start justify-between mb-3">
        <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center text-lg">💵</div>
        <span
          v-if="incomeChange !== null"
          :class="[
            'inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-lg',
            incomeChange >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700',
          ]"
        >
          {{ incomeChange >= 0 ? '↑' : '↓' }} {{ Math.abs(incomeChange) }}%
        </span>
      </div>
      <p class="text-xs text-gray-400 mb-1">Pemasukan Bulan Ini</p>
      <p class="text-xl font-bold text-gray-900">{{ formatRupiah(currentMonth?.income ?? 0) }}</p>
      <p class="text-xs mt-1.5" :class="incomeChange >= 0 ? 'text-green-500' : 'text-red-500'">
        <template v-if="incomeChange > 0">
          Naik {{ Math.abs(incomeChange) }}% dari bulan lalu
        </template>
        <template v-else-if="incomeChange < 0">
          Turun {{ Math.abs(incomeChange) }}% dari bulan lalu
        </template>
        <template v-else>
          Sama dengan bulan lalu
        </template>
      </p>
    </div>

    <!-- Rasio pengeluaran -->
    <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md transition-shadow">
      <div class="flex items-start justify-between mb-3">
        <div class="w-9 h-9 bg-violet-100 rounded-xl flex items-center justify-center text-lg">🎯</div>
        <span
          :class="[
            'inline-flex items-center text-xs font-semibold px-2 py-1 rounded-lg',
            spendingRatio > 80 ? 'bg-red-100 text-red-700' :
            spendingRatio > 50 ? 'bg-yellow-100 text-yellow-700' :
                                  'bg-green-100 text-green-700',
          ]"
        >
          {{ spendingRatio }}%
        </span>
      </div>
      <p class="text-xs text-gray-400 mb-1">Rasio Pengeluaran</p>
      <p class="text-xl font-bold text-gray-900">{{ spendingRatio }}%</p>
      <!-- Progress bar -->
      <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
        <div
          :class="[
            'h-full rounded-full transition-all duration-700',
            spendingRatio > 80 ? 'bg-red-500' :
            spendingRatio > 50 ? 'bg-yellow-500' : 'bg-green-500',
          ]"
          :style="{ width: `${Math.min(spendingRatio, 100)}%` }"
        ></div>
      </div>
      <p class="text-xs text-gray-400 mt-1.5">dari total pemasukan bulan ini</p>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  currentMonth:  { type: Object, default: null },
  lastMonth:     { type: Object, default: null },
  expenseChange: { type: Number, default: 0 },
  incomeChange:  { type: Number, default: 0 },
})

const spendingRatio = computed(() => {
  const income  = parseFloat(props.currentMonth?.income ?? 0)
  const expense = parseFloat(props.currentMonth?.expense ?? 0)
  if (income === 0) return 0
  return Math.round((expense / income) * 100)
})

function formatRupiah(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', minimumFractionDigits: 0,
  }).format(parseFloat(value) || 0)
}
</script>
