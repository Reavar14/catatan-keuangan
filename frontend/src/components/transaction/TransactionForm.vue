<template>
  <form @submit.prevent="handleSubmit" class="space-y-4">
    <!-- Judul -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Judul Transaksi</label>
      <input v-model="form.title" type="text" placeholder="Contoh: Gaji Bulan Ini"
        class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200"
        :disabled="isLoading" />
    </div>

    <!-- Tipe -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Transaksi</label>
      <select v-model="form.type"
        class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200"
        :disabled="isLoading">
        <option value="">-- Pilih Tipe --</option>
        <option value="income">Pemasukan</option>
        <option value="expense">Pengeluaran</option>
      </select>
    </div>

    <!-- Kategori (difilter berdasarkan tipe) -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
      <select v-model="form.category_id"
        class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200"
        :disabled="isLoading || !form.type">
        <option value="">-- Pilih Kategori --</option>
        <option v-for="cat in filteredCategories" :key="cat.id" :value="cat.id">
          {{ cat.name }}
        </option>
      </select>
    </div>

    <!-- Nominal -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Nominal (Rp)</label>
      <input v-model="form.amount" type="number" min="1" step="any" placeholder="Contoh: 500000"
        class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200"
        :disabled="isLoading" />
    </div>

    <!-- Tanggal -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Transaksi</label>
      <input v-model="form.transaction_date" type="date"
        class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200"
        :disabled="isLoading" />
    </div>

    <!-- Catatan -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Catatan <span class="text-gray-400">(opsional)</span></label>
      <textarea v-model="form.notes" rows="2" placeholder="Catatan tambahan..."
        class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 resize-none"
        :disabled="isLoading" />
    </div>

    <div class="flex gap-3 pt-2">
      <button type="submit" :disabled="isLoading"
        class="flex-1 py-2 px-4 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-50 transition-colors">
        {{ isLoading ? 'Menyimpan...' : (transaction ? 'Simpan Perubahan' : 'Tambah') }}
      </button>
      <button type="button" @click="$emit('cancel')" :disabled="isLoading"
        class="flex-1 py-2 px-4 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 disabled:opacity-50 transition-colors">
        Batal
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useCategoryStore } from '@/stores/categoryStore'

const props = defineProps({
  transaction: { type: Object, default: null },
  isLoading:   { type: Boolean, default: false },
})
const emit = defineEmits(['submitted', 'cancel'])

const categoryStore = useCategoryStore()

const form = ref({
  title: '', type: '', category_id: '', amount: '', transaction_date: '', notes: '',
})

watch(() => props.transaction, (val) => {
  if (val) {
    form.value = {
      title:            val.title,
      type:             val.type,
      category_id:      val.category?.id ?? val.category_id,
      amount:           val.amount,
      transaction_date: val.transaction_date,
      notes:            val.notes ?? '',
    }
  } else {
    form.value = { title: '', type: '', category_id: '', amount: '', transaction_date: '', notes: '' }
  }
}, { immediate: true })

// Reset category_id saat tipe berubah
watch(() => form.value.type, () => {
  form.value.category_id = ''
})

const filteredCategories = computed(() => {
  if (!form.value.type) return []
  return form.value.type === 'income'
    ? categoryStore.incomeCategories
    : categoryStore.expenseCategories
})

function handleSubmit() {
  emit('submitted', { ...form.value })
}
</script>
