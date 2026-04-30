import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useTransactionStore = defineStore('transaction', () => {
  const transactions = ref([])
  const meta         = ref(null)
  const isLoading    = ref(false)

  const filters = ref({
    type: '',
    category_id: '',
    transaction_date_from: '',
    transaction_date_to: '',
    search: '',
  })

  const sorting = ref({
    sort_by: 'transaction_date',
    sort_order: 'desc',
  })

  const currentPage = ref(1)

  function _buildParams() {
    const params = {
      page: currentPage.value,
      per_page: 10,
      sort_by: sorting.value.sort_by,
      sort_order: sorting.value.sort_order,
    }
    if (filters.value.type)                   params.type = filters.value.type
    if (filters.value.category_id)            params.category_id = filters.value.category_id
    if (filters.value.transaction_date_from)  params.transaction_date_from = filters.value.transaction_date_from
    if (filters.value.transaction_date_to)    params.transaction_date_to = filters.value.transaction_date_to
    if (filters.value.search)                 params.search = filters.value.search
    return params
  }

  async function fetchTransactions() {
    isLoading.value = true
    try {
      const { data } = await api.get('/transactions', { params: _buildParams() })
      transactions.value = data.data.data ?? data.data
      meta.value         = data.meta
    } finally {
      isLoading.value = false
    }
  }

  function setFilter(key, value) {
    filters.value[key] = value
    currentPage.value  = 1
    fetchTransactions()
  }

  function resetFilters() {
    filters.value  = { type: '', category_id: '', transaction_date_from: '', transaction_date_to: '', search: '' }
    sorting.value  = { sort_by: 'transaction_date', sort_order: 'desc' }
    currentPage.value = 1
    fetchTransactions()
  }

  function setPage(page) {
    currentPage.value = page
    fetchTransactions()
  }

  function setSorting(by, order) {
    sorting.value.sort_by    = by
    sorting.value.sort_order = order
    currentPage.value        = 1
    fetchTransactions()
  }

  async function createTransaction(payload) {
    isLoading.value = true
    try {
      await api.post('/transactions', payload)
      currentPage.value = 1
      await fetchTransactions()
    } finally {
      isLoading.value = false
    }
  }

  async function updateTransaction(id, payload) {
    isLoading.value = true
    try {
      await api.put(`/transactions/${id}`, payload)
      await fetchTransactions()
    } finally {
      isLoading.value = false
    }
  }

  async function deleteTransaction(id) {
    isLoading.value = true
    try {
      await api.delete(`/transactions/${id}`)
      await fetchTransactions()
    } finally {
      isLoading.value = false
    }
  }

  return {
    transactions,
    meta,
    isLoading,
    filters,
    sorting,
    currentPage,
    fetchTransactions,
    setFilter,
    resetFilters,
    setPage,
    setSorting,
    createTransaction,
    updateTransaction,
    deleteTransaction,
  }
})
