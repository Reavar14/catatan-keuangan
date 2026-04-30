import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  const user      = ref(JSON.parse(localStorage.getItem('user')) || null)
  const token     = ref(localStorage.getItem('token') || null)
  const isLoading = ref(false)
  const errors    = ref({})

  const isAuthenticated = computed(() => !!token.value)

  function _saveSession(userData, tokenValue) {
    user.value  = userData
    token.value = tokenValue
    localStorage.setItem('user', JSON.stringify(userData))
    localStorage.setItem('token', tokenValue)
    errors.value = {}
  }

  function _clearSession() {
    user.value  = null
    token.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('token')
  }

  async function register(payload) {
    isLoading.value = true
    errors.value    = {}
    try {
      const { data } = await api.post('/register', payload)
      _saveSession(data.data.user, data.data.token)
      router.push('/dashboard')
    } catch (err) {
      errors.value = err.response?.data?.errors || {}
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function login(payload) {
    isLoading.value = true
    errors.value    = {}
    try {
      const { data } = await api.post('/login', payload)
      _saveSession(data.data.user, data.data.token)
      router.push('/dashboard')
    } catch (err) {
      errors.value = err.response?.data?.errors || {}
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function logout() {
    isLoading.value = true
    try {
      await api.post('/logout')
    } catch (_) {
      // tetap lanjutkan logout meski request gagal
    } finally {
      _clearSession()
      isLoading.value = false
      router.push('/login')
    }
  }

  function initAuth() {
    const savedToken = localStorage.getItem('token')
    const savedUser  = localStorage.getItem('user')
    if (savedToken && savedUser) {
      token.value = savedToken
      user.value  = JSON.parse(savedUser)
    }
  }

  return {
    user,
    token,
    isLoading,
    errors,
    isAuthenticated,
    register,
    login,
    logout,
    initAuth,
  }
})
