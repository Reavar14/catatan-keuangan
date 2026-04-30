import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useDashboardStore = defineStore('dashboard', () => {
  const summary      = ref({ total_income: '0.00', total_expense: '0.00', balance: '0.00' })
  const monthlyChart = ref([])
  const isLoading    = ref(false)

  // ─── Insight: bulan ini vs bulan lalu ────────────────────────────────────
  const currentMonthData = computed(() => {
    if (!monthlyChart.value.length) return null
    const now = new Date()
    const key = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
    return monthlyChart.value.find(m => m.month === key) ?? null
  })

  const lastMonthData = computed(() => {
    if (!monthlyChart.value.length) return null
    const d = new Date()
    d.setMonth(d.getMonth() - 1)
    const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
    return monthlyChart.value.find(m => m.month === key) ?? null
  })

  const expenseChange = computed(() => {
    const curr = parseFloat(currentMonthData.value?.expense ?? 0)
    const prev = parseFloat(lastMonthData.value?.expense ?? 0)
    if (prev === 0) return curr > 0 ? 100 : 0
    return Math.round(((curr - prev) / prev) * 100)
  })

  const incomeChange = computed(() => {
    const curr = parseFloat(currentMonthData.value?.income ?? 0)
    const prev = parseFloat(lastMonthData.value?.income ?? 0)
    if (prev === 0) return curr > 0 ? 100 : 0
    return Math.round(((curr - prev) / prev) * 100)
  })

  // Kategori pengeluaran terbesar bulan ini (dari chart — approximasi)
  const topExpenseMonth = computed(() => {
    if (!currentMonthData.value) return null
    return {
      expense: parseFloat(currentMonthData.value.expense),
      income:  parseFloat(currentMonthData.value.income),
      month:   currentMonthData.value.month,
    }
  })

  async function fetchDashboard() {
    isLoading.value = true
    try {
      const { data } = await api.get('/dashboard')
      summary.value      = {
        total_income:  data.data.total_income,
        total_expense: data.data.total_expense,
        balance:       data.data.balance,
      }
      monthlyChart.value = data.data.monthly_chart
    } finally {
      isLoading.value = false
    }
  }

  return {
    summary,
    monthlyChart,
    isLoading,
    currentMonthData,
    lastMonthData,
    expenseChange,
    incomeChange,
    topExpenseMonth,
    fetchDashboard,
  }
})
