<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none">
      <TransitionGroup name="toast">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="[
            'flex items-center gap-3 px-4 py-3 rounded-2xl shadow-lg text-sm font-medium pointer-events-auto max-w-sm',
            toast.type === 'success' ? 'bg-gray-900 text-white' :
            toast.type === 'error'   ? 'bg-red-600 text-white' :
                                       'bg-indigo-600 text-white',
          ]"
        >
          <span class="text-base flex-shrink-0">
            {{ toast.type === 'success' ? '✓' : toast.type === 'error' ? '✕' : 'ℹ' }}
          </span>
          <span class="flex-1">{{ toast.message }}</span>
          <button
            @click="dismiss(toast.id)"
            class="ml-1 opacity-60 hover:opacity-100 transition-opacity text-xs"
          >✕</button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { useToast } from '@/composables/useToast'
const { toasts, dismiss } = useToast()
</script>

<style scoped>
.toast-enter-active { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.toast-leave-active { transition: all 0.2s ease; }
.toast-enter-from   { opacity: 0; transform: translateX(100%) scale(0.9); }
.toast-leave-to     { opacity: 0; transform: translateX(100%); }
.toast-move         { transition: transform 0.3s ease; }
</style>
