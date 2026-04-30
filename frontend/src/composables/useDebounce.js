import { ref, watch } from 'vue'

/**
 * Returns a debounced ref that updates after `delay` ms of inactivity.
 * @param {import('vue').Ref} source
 * @param {number} delay
 */
export function useDebounce(source, delay = 400) {
  const debounced = ref(source.value)
  let timer = null

  watch(source, (val) => {
    clearTimeout(timer)
    timer = setTimeout(() => {
      debounced.value = val
    }, delay)
  })

  return debounced
}
