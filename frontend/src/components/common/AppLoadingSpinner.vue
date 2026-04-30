<template>
  <div v-if="isLoading" class="flex items-center justify-center py-12">
    <div class="flex flex-col items-center gap-3">
      <div class="w-10 h-10 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
      <p class="text-sm text-gray-500">{{ message }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  isLoading: {
    type: Boolean,
    default: false,
  },
  message: {
    type: String,
    default: 'Memuat data...',
  },
})
</script>
