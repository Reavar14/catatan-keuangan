import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useCategoryStore = defineStore('category', () => {
  const categories = ref([])
  const isLoading  = ref(false)

  const incomeCategories  = computed(() => categories.value.filter(c => c.type === 'income'))
  const expenseCategories = computed(() => categories.value.filter(c => c.type === 'expense'))

  async function fetchCategories() {
    isLoading.value = true
    try {
      const { data } = await api.get('/categories')
      categories.value = data.data
    } finally {
      isLoading.value = false
    }
  }

  async function createCategory(payload) {
    isLoading.value = true
    try {
      const { data } = await api.post('/categories', payload)
      categories.value.push(data.data)
    } finally {
      isLoading.value = false
    }
  }

  async function updateCategory(id, payload) {
    isLoading.value = true
    try {
      const { data } = await api.put(`/categories/${id}`, payload)
      const idx = categories.value.findIndex(c => c.id === id)
      if (idx !== -1) categories.value[idx] = data.data
    } finally {
      isLoading.value = false
    }
  }

  async function deleteCategory(id) {
    isLoading.value = true
    try {
      await api.delete(`/categories/${id}`)
      categories.value = categories.value.filter(c => c.id !== id)
    } finally {
      isLoading.value = false
    }
  }

  return {
    categories,
    isLoading,
    incomeCategories,
    expenseCategories,
    fetchCategories,
    createCategory,
    updateCategory,
    deleteCategory,
  }
})
