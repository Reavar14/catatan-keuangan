<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow-sm border border-gray-200 p-8">
      <div class="text-center mb-8">
        <div class="text-4xl mb-2">💰</div>
        <h1 class="text-2xl font-bold text-gray-900">Daftar Akun</h1>
        <p class="text-sm text-gray-500 mt-1">Catatan Keuangan Pribadi</p>
      </div>

      <AppAlert :message="errorMessage" type="error" class="mb-4" />

      <form @submit.prevent="handleRegister" class="space-y-4">
        <!-- Nama -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
          <input
            v-model="form.name"
            type="text"
            placeholder="Nama lengkap"
            :class="inputClass(authStore.errors?.name)"
            :disabled="authStore.isLoading"
          />
          <p v-if="authStore.errors?.name" class="mt-1 text-xs text-red-600">
            {{ authStore.errors.name[0] }}
          </p>
        </div>

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

        <!-- Konfirmasi Password -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Kata Sandi</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            placeholder="Ulangi kata sandi"
            :class="inputClass(authStore.errors?.password_confirmation)"
            :disabled="authStore.isLoading"
          />
          <p v-if="authStore.errors?.password_confirmation" class="mt-1 text-xs text-red-600">
            {{ authStore.errors.password_confirmation[0] }}
          </p>
        </div>

        <button
          type="submit"
          :disabled="authStore.isLoading"
          class="w-full py-2.5 px-4 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          {{ authStore.isLoading ? 'Memproses...' : 'Daftar' }}
        </button>
      </form>

      <p class="text-center text-sm text-gray-500 mt-6">
        Sudah punya akun?
        <RouterLink to="/login" class="text-indigo-600 font-medium hover:underline">Masuk</RouterLink>
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
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

function inputClass(hasError) {
  return [
    'w-full px-3 py-2 rounded-lg border text-sm focus:outline-none focus:ring-2 transition-colors',
    hasError
      ? 'border-red-400 focus:ring-red-200'
      : 'border-gray-300 focus:ring-indigo-200 focus:border-indigo-400',
  ]
}

async function handleRegister() {
  errorMessage.value = ''
  try {
    await authStore.register(form.value)
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Registrasi gagal. Silakan coba lagi.'
  }
}
</script>
