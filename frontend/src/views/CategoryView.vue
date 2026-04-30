<template>
  <div class="min-h-screen bg-gray-50/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Kategori</h1>
          <p class="text-sm text-gray-500 mt-0.5">Kelola kategori pemasukan dan pengeluaran</p>
        </div>
        <button
          @click="openForm()"
          class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 active:scale-95 transition-all shadow-sm shadow-indigo-200"
        >
          <span class="text-base leading-none">+</span>
          Tambah Kategori
        </button>
      </div>

      <!-- Alert -->
      <!-- Digantikan oleh AppToast global -->

      <!-- Loading skeleton -->
      <template v-if="categoryStore.isLoading">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="i in 6" :key="i" class="h-20 bg-gray-200 rounded-2xl animate-pulse"></div>
        </div>
      </template>

      <template v-else>
        <!-- Empty state -->
        <div v-if="categoryStore.categories.length === 0" class="flex flex-col items-center justify-center py-20 gap-4">
          <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center text-4xl">🏷️</div>
          <div class="text-center">
            <p class="font-semibold text-gray-700">Belum ada kategori</p>
            <p class="text-sm text-gray-400 mt-1">Tambahkan kategori untuk mengorganisir transaksi</p>
          </div>
        </div>

        <!-- Category grid -->
        <div v-else class="space-y-6">
          <!-- Pemasukan -->
          <div v-if="categoryStore.incomeCategories.length > 0">
            <div class="flex items-center gap-2 mb-3">
              <span class="w-2 h-2 rounded-full bg-green-500"></span>
              <h2 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Pemasukan</h2>
              <span class="text-xs text-gray-400">({{ categoryStore.incomeCategories.length }})</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
              <CategoryCard
                v-for="cat in categoryStore.incomeCategories"
                :key="cat.id"
                :category="cat"
                @edit="openForm"
                @delete="handleDelete"
              />
            </div>
          </div>

          <!-- Pengeluaran -->
          <div v-if="categoryStore.expenseCategories.length > 0">
            <div class="flex items-center gap-2 mb-3">
              <span class="w-2 h-2 rounded-full bg-red-500"></span>
              <h2 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Pengeluaran</h2>
              <span class="text-xs text-gray-400">({{ categoryStore.expenseCategories.length }})</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
              <CategoryCard
                v-for="cat in categoryStore.expenseCategories"
                :key="cat.id"
                :category="cat"
                @edit="openForm"
                @delete="handleDelete"
              />
            </div>
          </div>
        </div>
      </template>

    </div>
  </div>

  <!-- Modal Form -->
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeForm"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">
              {{ editingCategory ? 'Edit Kategori' : 'Tambah Kategori' }}
            </h2>
            <button @click="closeForm" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">✕</button>
          </div>
          <div class="p-6">
            <CategoryForm
              :category="editingCategory"
              :is-loading="categoryStore.isLoading"
              @submitted="handleSubmit"
              @cancel="closeForm"
            />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useCategoryStore } from '@/stores/categoryStore'
import AppAlert from '@/components/common/AppAlert.vue'
import CategoryCard from '@/components/category/CategoryCard.vue'
import CategoryForm from '@/components/category/CategoryForm.vue'
import { useToast }   from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'

const categoryStore   = useCategoryStore()
const toast           = useToast()
const { confirm }     = useConfirm()
const showForm        = ref(false)
const editingCategory = ref(null)
const alertMessage    = ref('')
const alertType       = ref('success')

onMounted(() => categoryStore.fetchCategories())

function openForm(category = null) {
  editingCategory.value = category
  showForm.value = true
}

function closeForm() {
  showForm.value = false
  editingCategory.value = null
}

async function handleSubmit(payload) {
  try {
    if (editingCategory.value) {
      await categoryStore.updateCategory(editingCategory.value.id, payload)
      toast.success('Kategori berhasil diperbarui.')
    } else {
      await categoryStore.createCategory(payload)
      toast.success('Kategori berhasil ditambahkan.')
    }
    closeForm()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Terjadi kesalahan.')
  }
}

async function handleDelete(id) {
  const ok = await confirm({
    title:        'Hapus Kategori?',
    message:      'Kategori tidak dapat dihapus jika masih digunakan oleh transaksi.',
    confirmLabel: 'Ya, Hapus',
  })
  if (!ok) return
  try {
    await categoryStore.deleteCategory(id)
    toast.success('Kategori berhasil dihapus.')
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menghapus kategori.')
  }
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
