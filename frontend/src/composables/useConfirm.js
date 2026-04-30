import { ref } from 'vue'

const state = ref({
  visible: false,
  title: '',
  message: '',
  confirmLabel: 'Hapus',
  confirmClass: 'bg-red-600 hover:bg-red-700 text-white',
  resolve: null,
})

export function useConfirm() {
  function confirm({ title = 'Konfirmasi', message, confirmLabel = 'Hapus', confirmClass } = {}) {
    return new Promise((resolve) => {
      state.value = {
        visible: true,
        title,
        message,
        confirmLabel,
        confirmClass: confirmClass ?? 'bg-red-600 hover:bg-red-700 text-white',
        resolve,
      }
    })
  }

  function _answer(result) {
    state.value.resolve?.(result)
    state.value.visible = false
  }

  return { confirmState: state, confirm, _answer }
}
