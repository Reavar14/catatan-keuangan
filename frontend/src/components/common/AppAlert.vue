<template>
  <Transition name="alert">
    <div
      v-if="message"
      :class="[
        'flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium border',
        type === 'success'
          ? 'bg-green-50 text-green-800 border-green-200'
          : 'bg-red-50 text-red-800 border-red-200',
      ]"
    >
      <span class="text-base flex-shrink-0">{{ type === 'success' ? '✅' : '❌' }}</span>
      <span class="flex-1">{{ message }}</span>
    </div>
  </Transition>
</template>

<script setup>
defineProps({
  type:    { type: String, default: 'error', validator: (v) => ['success', 'error'].includes(v) },
  message: { type: String, default: '' },
})
</script>

<style scoped>
.alert-enter-active, .alert-leave-active { transition: all 0.25s ease; }
.alert-enter-from, .alert-leave-to { opacity: 0; transform: translateY(-6px); }
</style>
