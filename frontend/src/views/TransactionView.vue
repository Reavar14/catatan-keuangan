<template>
  <div class="min-h-screen bg-gray-50/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Transaksi</h1>
          <p class="text-sm text-gray-500 mt-0.5">Kelola dan analisis semua transaksi keuangan Anda</p>
        </div>
        <button
          @click="openForm()"
          :disabled="transactionStore.isLoading"
          class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 active:scale-95 disabled:opacity-50 transition-all shadow-sm shadow-indigo-200 whitespace-nowrap"
        >
          <span class="text-base leading-none">+</span>
          Tambah Transaksi
        </button>
      </div>

      <!-- Filter Panel -->
      <TransactionFilter
        :filters="transactionStore.filters"
        :sorting="transactionStore.sorting"
        :is-loading="transactionStore.isLoading"
        @filter-change="handleFilterChange"
        @reset="transactionStore.resetFilters()"
      />

      <!-- Summary Bar -->
      <Transition name="fade">
        <div
          v-if="transactionStore.meta && !transactionStore.isLoading"
          class="mt-3 flex flex-wrap items-center justify-between gap-3 px-1"
        >
          <!-- Kiri: info jumlah -->
          <p class="text-sm text-gray-500">
            Menampilkan
            <span class="font-semibold text-gray-800">{{ transactionStore.meta.from ?? 0 }}–{{ transactionStore.meta.to ?? 0 }}</span>
            dari
            <span class="font-semibold text-gray-800">{{ transactionStore.meta.total }}</span>
            transaksi
            <template v-if="hasActiveFilters">
              <span class="text-indigo-500 font-medium"> (difilter)</span>
            </template>
          </p>

          <!-- Kanan: ringkasan nominal halaman ini -->
          <div class="flex items-center gap-4 text-sm">
            <span class="flex items-center gap-1.5 text-green-600 font-medium">
              <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
              +{{ formatRupiah(pageIncome) }}
            </span>
            <span class="flex items-center gap-1.5 text-red-500 font-medium">
              <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
              -{{ formatRupiah(pageExpense) }}
            </span>
            <span class="text-gray-400">|</span>
            <span
              :class="['font-semibold', pageNet >= 0 ? 'text-green-600' : 'text-red-500']"
            >
              {{ pageNet >= 0 ? '+' : '' }}{{ formatRupiah(pageNet) }}
            </span>
          </div>
        </div>
      </Transition>

      <!-- Table Card -->
      <div class="mt-3 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden relative">

        <!-- Loading overlay (bukan replace content, tapi overlay tipis) -->
        <Transition name="fade">
          <div
            v-if="transactionStore.isLoading"
            class="absolute inset-0 bg-white/70 backdrop-blur-[1px] z-10 flex items-center justify-center"
          >
            <div class="flex flex-col items-center gap-2">
              <div class="w-8 h-8 border-3 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
              <p class="text-xs text-gray-500">Memuat...</p>
            </div>
          </div>
        </Transition>

        <!-- Skeleton saat pertama kali load (belum ada data sama sekali) -->
        <template v-if="transactionStore.isLoading && transactionStore.transactions.length === 0">
          <div class="divide-y divide-gray-50">
            <div v-for="i in 6" :key="i" class="flex items-center gap-4 px-5 py-4">
              <div class="w-9 h-9 bg-gray-200 rounded-xl animate-pulse flex-shrink-0"></div>
              <div class="flex-1 space-y-2">
                <div class="h-3 bg-gray-200 rounded animate-pulse" :style="`width: ${30 + (i * 7) % 40}%`"></div>
                <div class="h-2.5 bg-gray-100 rounded animate-pulse" :style="`width: ${15 + (i * 5) % 25}%`"></div>
              </div>
              <div class="h-4 bg-gray-200 rounded animate-pulse w-24"></div>
            </div>
          </div>
        </template>

        <TransactionTable
          v-else
          :transactions="transactionStore.transactions"
          @edit="openForm"
          @delete="handleDelete"
        />
      </div>

      <!-- Pagination -->
      <AppPagination
        :meta="transactionStore.meta"
        :disabled="transactionStore.isLoading"
        @page-change="transactionStore.setPage($event)"
      />

    </div>
  </div>

  <!-- Modal Form -->
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeForm"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">
              {{ editingTransaction ? 'Edit Transaksi' : 'Tambah Transaksi' }}
            </h2>
            <button
              @click="closeForm"
              class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors"
            >✕</button>
          </div>
          <div class="p-6">
            <TransactionForm
              :transaction="editingTransaction"
              :is-loading="isSubmitting"
              @submitted="handleSubmit"
              @cancel="closeForm"
            />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useTransactionStore } from '@/stores/transactionStore'
import { useCategoryStore }    from '@/stores/categoryStore'
import { useToast }            from '@/composables/useToast'
import { useConfirm }          from '@/composables/useConfirm'
import AppPagination           from '@/components/common/AppPagination.vue'
import TransactionFilter       from '@/components/transaction/TransactionFilter.vue'
import TransactionTable        from '@/components/transaction/TransactionTable.vue'
import TransactionForm         from '@/components/transaction/TransactionForm.vue'

const transactionStore   = useTransactionStore()
const categoryStore      = useCategoryStore()
const toast              = useToast()
const { confirm }        = useConfirm()

const showForm           = ref(false)
const editingTransaction = ref(null)
const isSubmitting       = ref(false)

onMounted(() => {
  transactionStore.fetchTransactions()
  categoryStore.fetchCategories()
})

// ─── Summary bar kalkulasi dari data halaman saat ini ─────────────────────────
const pageIncome = computed(() =>
  transactionStore.transactions
    .filter(t => t.type === 'income')
    .reduce((sum, t) => sum + parseFloat(t.amount || 0), 0)
)
const pageExpense = computed(() =>
  transactionStore.transactions
    .filter(t => t.type === 'expense')
    .reduce((sum, t) => sum + parseFloat(t.amount || 0), 0)
)
const pageNet = computed(() => pageIncome.value - pageExpense.value)

const hasActiveFilters = computed(() =>
  !!(transactionStore.filters.type ||
     transactionStore.filters.category_id ||
     transactionStore.filters.transaction_date_from ||
     transactionStore.filters.transaction_date_to ||
     transactionStore.filters.search)
)

function formatRupiah(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', minimumFractionDigits: 0,
  }).format(Math.abs(parseFloat(value) || 0))
}

// ─── Form ─────────────────────────────────────────────────────────────────────
function openForm(transaction = null) {
  editingTransaction.value = transaction
  showForm.value = true
}

function closeForm() {
  showForm.value = false
  editingTransaction.value = null
}

function handleFilterChange(key, value) {
  transactionStore.setFilter(key, value)
}

async function handleSubmit(payload) {
  isSubmitting.value = true
  try {
    if (editingTransaction.value) {
      await transactionStore.updateTransaction(editingTransaction.value.id, payload)
      toast.success('Transaksi berhasil diperbarui.')
    } else {
      await transactionStore.createTransaction(payload)
      toast.success('Transaksi berhasil ditambahkan.')
    }
    closeForm()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Terjadi kesalahan. Coba lagi.')
  } finally {
    isSubmitting.value = false
  }
}

async function handleDelete(id) {
  const ok = await confirm({
    title:        'Hapus Transaksi?',
    message:      'Transaksi ini akan dihapus permanen dan tidak dapat dikembalikan.',
    confirmLabel: 'Ya, Hapus',
  })
  if (!ok) return
  try {
    await transactionStore.deleteTransaction(id)
    toast.success('Transaksi berhasil dihapus.')
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menghapus transaksi.')
  }
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-active .relative { transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); }
.modal-enter-from .relative { transform: scale(0.95) translateY(8px); }

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
