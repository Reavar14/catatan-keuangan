<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow-sm border border-gray-200 p-8">
      <div class="text-center mb-8">
        <div class="text-4xl mb-2">💰</div>
        <h1 class="text-2xl font-bold text-gray-900">Masuk</h1>
        <p class="text-sm text-gray-500 mt-1">Catatan Keuangan Pribadi</p>
      </div>

      <AppAlert :message="errorMessage" type="error" class="mb-4" />

      <form @submit.prevent="handleLogin" class="space-y-4">
        <!-- Email -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            placeholder="email@contoh.com"
            :class="inputClass(authStore.errors?.email)"
            :disabled="authStore.isLoading"
          />
          <p v-if="authStore.errors?.email" class="mt-1 text-xs text-red-600">
            {{ authStore.errors.email[0] }}
          </p>
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
          <input
            v-model="form.password"
            type="password"
            placeholder="Minimal 8 karakter"
            :class="inputClass(authStore.errors?.password)"
            :disabled="authStore.isLoading"
          />
          <p v-if="authStore.errors?.password" class="mt-1 text-xs text-red-600">
            {{ authStore.errors.password[0] }}
          </p>
        </div>

        <button
          type="submit"
          :disabled="authStore.isLoading"
          class="w-full py-2.5 px-4 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          {{ authStore.isLoading ? 'Memproses...' : 'Masuk' }}
        </button>
      </form>

      <p class="text-center text-sm text-gray-500 mt-6">
        Belum punya akun?
        <RouterLink to="/register" class="text-indigo-600 font-medium hover:underline">Daftar</RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import AppAlert from '@/components/common/AppAlert.vue'

const authStore = useAuthStore()
const errorMessage = ref('')

const form = ref({
  email: '',
  password: '',
})

function inputClass(hasError) {
  return [
    'w-full px-3 py-2 rounded-lg border text-sm focus:outline-none focus:ring-2 transition-colors',
    hasError
      ? 'border-red-400 focus:ring-red-200'
      : 'border-gray-300 focus:ring-indigo-200 focus:border-indigo-400',
  ]
}

async function handleLogin() {
  errorMessage.value = ''
  try {
    await authStore.login(form.value)
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Login gagal. Periksa kembali email dan kata sandi.'
  }
}
</script>
