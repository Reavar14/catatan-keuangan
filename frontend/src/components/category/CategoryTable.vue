<template>
  <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div v-if="categories.length === 0" class="flex items-center justify-center py-12 text-gray-400 text-sm">
      Belum ada kategori. Tambahkan kategori pertama Anda.
    </div>
    <table v-else class="w-full text-sm">
      <thead class="bg-gray-50 border-b border-gray-200">
        <tr>
          <th class="text-left px-4 py-3 font-medium text-gray-600">Nama Kategori</th>
          <th class="text-left px-4 py-3 font-medium text-gray-600">Tipe</th>
          <th class="text-right px-4 py-3 font-medium text-gray-600">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <tr v-for="category in categories" :key="category.id" class="hover:bg-gray-50 transition-colors">
          <td class="px-4 py-3 font-medium text-gray-900">{{ category.name }}</td>
          <td class="px-4 py-3">
            <span
              :class="[
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                category.type === 'income'
                  ? 'bg-green-100 text-green-800'
                  : 'bg-red-100 text-red-800',
              ]"
            >
              {{ category.type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
            </span>
          </td>
          <td class="px-4 py-3 text-right">
            <button
              @click="$emit('edit', category)"
              class="text-indigo-600 hover:text-indigo-800 font-medium mr-3 transition-colors"
            >
              Edit
            </button>
            <button
              @click="$emit('delete', category.id)"
              class="text-red-600 hover:text-red-800 font-medium transition-colors"
            >
              Hapus
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({
  categories: { type: Array, default: () => [] },
})
defineEmits(['edit', 'delete'])
</script>
