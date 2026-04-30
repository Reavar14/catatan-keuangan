<template>
  <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">

      <!-- Logo -->
      <RouterLink to="/dashboard" class="flex items-center gap-2 group">
        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-lg flex items-center justify-center shadow-sm group-hover:shadow-indigo-200 transition-shadow">
          <span class="text-white text-sm font-bold">₹</span>
        </div>
        <span class="font-bold text-gray-900 text-sm hidden sm:block">Catatan Keuangan</span>
      </RouterLink>

      <!-- Nav Links (desktop) -->
      <div class="hidden md:flex items-center gap-1 bg-gray-100 rounded-xl p-1">
        <RouterLink
          v-for="link in navLinks"
          :key="link.to"
          :to="link.to"
          class="flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-900 transition-all"
          active-class="bg-white text-gray-900 shadow-sm"
        >
          <span>{{ link.icon }}</span>
          {{ link.label }}
        </RouterLink>
      </div>

      <!-- User & Logout -->
      <div class="flex items-center gap-2">
        <!-- Avatar -->
        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-gray-50 rounded-xl border border-gray-200">
          <div class="w-6 h-6 bg-gradient-to-br from-indigo-400 to-violet-500 rounded-full flex items-center justify-center">
            <span class="text-white text-xs font-bold">{{ userInitial }}</span>
          </div>
          <span class="text-sm font-medium text-gray-700 max-w-[120px] truncate">{{ authStore.user?.name }}</span>
        </div>
        <button
          @click="handleLogout"
          :disabled="authStore.isLoading"
          class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm font-medium text-red-500 hover:bg-red-50 hover:text-red-600 border border-transparent hover:border-red-100 transition-all disabled:opacity-50"
        >
          <span>↩</span>
          <span class="hidden sm:block">Keluar</span>
        </button>
      </div>
    </div>

    <!-- Mobile nav -->
    <div class="md:hidden flex border-t border-gray-100 bg-white">
      <RouterLink
        v-for="link in navLinks"
        :key="link.to"
        :to="link.to"
        class="flex-1 flex flex-col items-center gap-0.5 py-2 text-xs font-medium text-gray-400 hover:text-indigo-600 transition-colors"
        active-class="text-indigo-600"
      >
        <span class="text-base">{{ link.icon }}</span>
        {{ link.label }}
      </RouterLink>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()

const navLinks = [
  { to: '/dashboard',    icon: '📊', label: 'Dashboard' },
  { to: '/categories',   icon: '🏷️',  label: 'Kategori' },
  { to: '/transactions', icon: '💳', label: 'Transaksi' },
]

const userInitial = computed(() =>
  authStore.user?.name?.charAt(0)?.toUpperCase() ?? 'U'
)

async function handleLogout() {
  await authStore.logout()
}
</script>
