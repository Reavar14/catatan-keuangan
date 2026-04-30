<template>
  <form @submit.prevent="handleSubmit" class="space-y-4">
    <!-- Nama -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori</label>
      <input
        v-model="form.name"
        type="text"
        placeholder="Contoh: Gaji, Makan, Transport"
        class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400"
        :disabled="isLoading"
      />
    </div>

    <!-- Tipe -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Kategori</label>
      <select
        v-model="form.type"
        class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400"
        :disabled="isLoading"
      >
        <option value="">-- Pilih Tipe --</option>
        <option value="income">Pemasukan</option>
        <option value="expense">Pengeluaran</option>
      </select>
    </div>

    <div class="flex gap-3 pt-2">
      <button
        type="submit"
        :disabled="isLoading"
        class="flex-1 py-2 px-4 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-50 transition-colors"
      >
        {{ isLoading ? 'Menyimpan...' : (category ? 'Simpan Perubahan' : 'Tambah') }}
      </button>
      <button
        type="button"
        @click="$emit('cancel')"
        :disabled="isLoading"
        class="flex-1 py-2 px-4 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 disabled:opacity-50 transition-colors"
      >
        Batal
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  category:  { type: Object, default: null },
  isLoading: { type: Boolean, default: false },
})
const emit = defineEmits(['submitted', 'cancel'])

const form = ref({ name: '', type: '' })

watch(() => props.category, (val) => {
  form.value = val ? { name: val.name, type: val.type } : { name: '', type: '' }
}, { immediate: true })

function handleSubmit() {
  emit('submitted', { ...form.value })
}
</script>
