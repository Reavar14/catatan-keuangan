<template>
  <div class="group relative bg-white rounded-2xl border border-gray-100 p-4 hover:border-gray-200 hover:shadow-md transition-all">
    <div class="flex items-center gap-3">
      <!-- Icon -->
      <div
        :class="[
          'w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0',
          category.type === 'income' ? 'bg-green-100' : 'bg-red-100',
        ]"
      >
        {{ category.type === 'income' ? '📈' : '📉' }}
      </div>

      <!-- Info -->
      <div class="flex-1 min-w-0">
        <p class="font-semibold text-gray-900 text-sm truncate">{{ category.name }}</p>
        <span
          :class="[
            'inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium mt-0.5',
            category.type === 'income'
              ? 'bg-green-100 text-green-700'
              : 'bg-red-100 text-red-700',
          ]"
        >
          {{ category.type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
        </span>
      </div>

      <!-- Actions (visible on hover) -->
      <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
        <button
          @click="$emit('edit', category)"
          class="w-7 h-7 flex items-center justify-center rounded-lg text-indigo-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors text-sm"
          title="Edit"
        >✏️</button>
        <button
          @click="$emit('delete', category.id)"
          class="w-7 h-7 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors text-sm"
          title="Hapus"
        >🗑️</button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({ category: { type: Object, required: true } })
defineEmits(['edit', 'delete'])
</script>
