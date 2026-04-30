<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <AppNavbar v-if="authStore.isAuthenticated" />
    <main :class="authStore.isAuthenticated ? 'pt-16 md:pt-16 pb-16 md:pb-0' : ''">
      <RouterView />
    </main>
    <!-- Global UI -->
    <AppToast />
    <AppConfirm />
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/authStore'
import AppNavbar  from '@/components/common/AppNavbar.vue'
import AppToast   from '@/components/common/AppToast.vue'
import AppConfirm from '@/components/common/AppConfirm.vue'

const authStore = useAuthStore()
</script>
