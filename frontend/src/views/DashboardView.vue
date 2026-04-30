<template>
  <div class="min-h-screen bg-gray-50/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

      <!-- Header -->
      <div class="flex items-start justify-between mb-8">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">
            Selamat datang, {{ authStore.user?.name?.split(' ')[0] }} 👋
          </h1>
          <p class="text-sm text-gray-500 mt-1">{{ todayLabel }}</p>
        </div>
        <button
          @click="refresh"
          :disabled="dashboardStore.isLoading"
          class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-gray-400 hover:bg-gray-50 hover:text-gray-600 disabled:opacity-40 transition-all"
          title="Refresh"
        >
          <span :class="dashboardStore.isLoading ? 'animate-spin' : ''">↻</span>
        </button>
      </div>

      <!-- Loading skeleton -->
      <template v-if="dashboardStore.isLoading">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
          <div v-for="i in 3" :key="i" class="h-28 bg-gray-200 rounded-2xl animate-pulse"></div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
          <div v-for="i in 3" :key="i" class="h-24 bg-gray-200 rounded-2xl animate-pulse"></div>
        </div>
        <div class="h-72 bg-gray-200 rounded-2xl animate-pulse mb-6"></div>
        <div class="h-64 bg-gray-200 rounded-2xl animate-pulse"></div>
      </template>

      <template v-else>
        <!-- Summary Cards -->
        <SummaryCard :summary="dashboardStore.summary" />

        <!-- Insight Cards -->
        <div class="mt-4">
          <div class="flex items-center gap-2 mb-3">
            <span class="text-sm font-semibold text-gray-500">📈 Insight Bulan Ini</span>
            <span v-if="dashboardStore.currentMonthData" class="text-xs text-gray-400">
              vs bulan lalu
            </span>
          </div>
          <InsightCard
            :current-month="dashboardStore.currentMonthData"
            :last-month="dashboardStore.lastMonthData"
            :expense-change="dashboardStore.expenseChange"
            :income-change="dashboardStore.incomeChange"
          />
        </div>

        <!-- Chart -->
        <div class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h2 class="text-base font-semibold text-gray-900">Grafik Transaksi Bulanan</h2>
              <p class="text-xs text-gray-400 mt-0.5">12 bulan terakhir</p>
            </div>
            <div class="flex items-center gap-3 text-xs text-gray-500">
              <span class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block"></span>Pemasukan
              </span>
              <span class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span>Pengeluaran
              </span>
            </div>
          </div>
          <MonthlyChart :chart-data="dashboardStore.monthlyChart" />
        </div>

        <!-- Recent Transactions -->
        <div class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Transaksi Terbaru</h2>
            <RouterLink
              to="/transactions"
              class="text-xs font-medium text-indigo-600 hover:text-indigo-700 transition-colors"
            >
              Lihat semua →
            </RouterLink>
          </div>
          <RecentTransactions />
        </div>
      </template>

    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useDashboardStore } from '@/stores/dashboardStore'
import { useAuthStore }      from '@/stores/authStore'
import SummaryCard           from '@/components/dashboard/SummaryCard.vue'
import InsightCard           from '@/components/dashboard/InsightCard.vue'
import MonthlyChart          from '@/components/dashboard/MonthlyChart.vue'
import RecentTransactions    from '@/components/dashboard/RecentTransactions.vue'

const dashboardStore = useDashboardStore()
const authStore      = useAuthStore()

const todayLabel = computed(() =>
  new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
)

function refresh() {
  dashboardStore.fetchDashboard()
}

onMounted(() => dashboardStore.fetchDashboard())
</script>
