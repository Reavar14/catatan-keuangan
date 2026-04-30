<template>
  <div v-if="chartData.length > 0" class="relative">
    <Bar :data="barData" :options="chartOptions" />
  </div>
  <div v-else class="flex flex-col items-center justify-center h-48 gap-3">
    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center text-3xl">📊</div>
    <p class="text-sm font-medium text-gray-500">Belum ada data grafik</p>
    <p class="text-xs text-gray-400">Tambahkan transaksi untuk melihat grafik bulanan</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

const props = defineProps({
  chartData: { type: Array, default: () => [] },
})

const barData = computed(() => ({
  labels: props.chartData.map(item => {
    const [year, month] = item.month.split('-')
    return new Date(year, month - 1).toLocaleDateString('id-ID', { month: 'short', year: '2-digit' })
  }),
  datasets: [
    {
      label: 'Pemasukan',
      data: props.chartData.map(item => parseFloat(item.income)),
      backgroundColor: 'rgba(34, 197, 94, 0.85)',
      borderColor: 'rgb(22, 163, 74)',
      borderWidth: 0,
      borderRadius: 6,
      borderSkipped: false,
    },
    {
      label: 'Pengeluaran',
      data: props.chartData.map(item => parseFloat(item.expense)),
      backgroundColor: 'rgba(239, 68, 68, 0.85)',
      borderColor: 'rgb(220, 38, 38)',
      borderWidth: 0,
      borderRadius: 6,
      borderSkipped: false,
    },
  ],
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: true,
  plugins: {
    legend: {
      position: 'top',
      labels: {
        usePointStyle: true,
        pointStyle: 'circle',
        padding: 20,
        font: { size: 12, family: 'system-ui' },
      },
    },
    tooltip: {
      backgroundColor: 'rgba(17, 24, 39, 0.9)',
      padding: 12,
      cornerRadius: 8,
      callbacks: {
        label: (ctx) => {
          const val = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(ctx.raw)
          return ` ${ctx.dataset.label}: ${val}`
        },
      },
    },
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { font: { size: 11 }, color: '#9ca3af' },
    },
    y: {
      grid: { color: 'rgba(243, 244, 246, 1)', lineWidth: 1 },
      border: { dash: [4, 4] },
      ticks: {
        font: { size: 11 },
        color: '#9ca3af',
        callback: (value) => {
          if (value >= 1000000) return `${(value / 1000000).toFixed(1)}jt`
          if (value >= 1000) return `${(value / 1000).toFixed(0)}rb`
          return value
        },
      },
    },
  },
}
</script>
