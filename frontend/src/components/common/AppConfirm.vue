<template>
  <Teleport to="body">
    <Transition name="confirm">
      <div v-if="confirmState.visible" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="_answer(false)"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
          <!-- Icon -->
          <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4">
            🗑️
          </div>
          <h3 class="text-base font-bold text-gray-900 text-center mb-2">
            {{ confirmState.title }}
          </h3>
          <p class="text-sm text-gray-500 text-center mb-6">
            {{ confirmState.message }}
          </p>
          <div class="flex gap-3">
            <button
              @click="_answer(false)"
              class="flex-1 py-2.5 px-4 rounded-xl border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Batal
            </button>
            <button
              @click="_answer(true)"
              :class="['flex-1 py-2.5 px-4 rounded-xl text-sm font-medium transition-colors', confirmState.confirmClass]"
            >
              {{ confirmState.confirmLabel }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { useConfirm } from '@/composables/useConfirm'
const { confirmState, _answer } = useConfirm()
</script>

<style scoped>
.confirm-enter-active, .confirm-leave-active { transition: opacity 0.2s ease; }
.confirm-enter-from, .confirm-leave-to { opacity: 0; }
.confirm-enter-active .relative { transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); }
.confirm-enter-from .relative { transform: scale(0.9); }
</style>
