<template>
  <div class="space-y-3">

    <!-- Row 1: Search (kiri) + Filter chips area (tengah) + Sort + Reset (kanan) -->
    <div class="flex flex-col lg:flex-row gap-3">

      <!-- Search -->
      <div class="relative flex-1 min-w-0">
        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-sm">🔍</span>
        <input
          :value="searchInput"
          @input="onSearch($event.target.value)"
          type="text"
          placeholder="Cari nama atau catatan transaksi..."
          :disabled="isLoading"
          class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-300 disabled:opacity-50 transition-all"
        />
        <!-- Clear search -->
        <button
          v-if="searchInput"
          @click="clearSearch"
          class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
        >✕</button>
      </div>

      <!-- Filter controls -->
      <div class="flex flex-wrap gap-2 items-center">

        <!-- Tipe -->
        <select
          :value="filters.type"
          @change="$emit('filter-change', 'type', $event.target.value)"
          :disabled="isLoading"
          :class="[
            'px-3 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-300 disabled:opacity-50 bg-white transition-all cursor-pointer',
            filters.type ? 'border-indigo-300 text-indigo-700 bg-indigo-50' : 'border-gray-200 text-gray-600',
          ]"
        >
          <option value="">Semua Tipe</option>
          <option value="income">📈 Pemasukan</option>
          <option value="expense">📉 Pengeluaran</option>
        </select>

        <!-- Kategori -->
        <select
          :value="filters.category_id"
          @change="$emit('filter-change', 'category_id', $event.target.value)"
          :disabled="isLoading"
          :class="[
            'px-3 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-300 disabled:opacity-50 bg-white transition-all cursor-pointer',
            filters.category_id ? 'border-indigo-300 text-indigo-700 bg-indigo-50' : 'border-gray-200 text-gray-600',
          ]"
        >
          <option value="">Semua Kategori</option>
          <option v-for="cat in categoryStore.categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>

        <!-- Date Range Trigger -->
        <button
          @click="showDatePicker = !showDatePicker"
          :disabled="isLoading"
          :class="[
            'flex items-center gap-2 px-3 py-2.5 rounded-xl border text-sm font-medium transition-all disabled:opacity-50',
            hasDateFilter
              ? 'border-indigo-300 text-indigo-700 bg-indigo-50'
              : 'border-gray-200 text-gray-600 bg-white hover:border-gray-300',
          ]"
        >
          <span>📅</span>
          <span>{{ dateRangeLabel }}</span>
          <span v-if="hasDateFilter" class="text-indigo-400 text-xs">▾</span>
        </button>

        <!-- Divider -->
        <div class="w-px h-6 bg-gray-200 hidden sm:block"></div>

        <!-- Sort (terpisah dari filter) -->
        <select
          :value="sortValue"
          @change="onSortChange($event.target.value)"
          :disabled="isLoading"
          class="px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-300 disabled:opacity-50 cursor-pointer"
        >
          <option value="transaction_date|desc">↓ Terbaru</option>
          <option value="transaction_date|asc">↑ Terlama</option>
          <option value="amount|desc">↓ Nominal Terbesar</option>
          <option value="amount|asc">↑ Nominal Terkecil</option>
        </select>

        <!-- Reset (hanya muncul jika ada filter aktif) -->
        <button
          v-if="hasActiveFilters"
          @click="$emit('reset')"
          :disabled="isLoading"
          class="flex items-center gap-1.5 px-3 py-2.5 rounded-xl border border-red-200 text-red-500 text-sm font-medium hover:bg-red-50 disabled:opacity-40 transition-all"
        >
          <span>✕</span>
          <span>Reset</span>
        </button>
      </div>
    </div>

    <!-- Date Range Picker (inline, collapsible) -->
    <Transition name="slide-down">
      <div v-if="showDatePicker" class="bg-white rounded-xl border border-indigo-100 p-4 shadow-sm">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
          <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Rentang Tanggal</span>
          <div class="flex items-center gap-2 flex-1">
            <div class="relative flex-1">
              <input
                type="date"
                :value="filters.transaction_date_from"
                @change="$emit('filter-change', 'transaction_date_from', $event.target.value)"
                :disabled="isLoading"
                class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-300 disabled:opacity-50"
              />
            </div>
            <span class="text-gray-400 font-medium text-sm flex-shrink-0">—</span>
            <div class="relative flex-1">
              <input
                type="date"
                :value="filters.transaction_date_to"
                :min="filters.transaction_date_from"
                @change="$emit('filter-change', 'transaction_date_to', $event.target.value)"
                :disabled="isLoading"
                class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-300 disabled:opacity-50"
              />
            </div>
          </div>
          <!-- Quick presets -->
          <div class="flex gap-1.5 flex-wrap">
            <button
              v-for="preset in datePresets"
              :key="preset.label"
              @click="applyPreset(preset)"
              class="px-2.5 py-1.5 rounded-lg text-xs font-medium border border-gray-200 text-gray-600 hover:border-indigo-300 hover:text-indigo-600 hover:bg-indigo-50 transition-all"
            >
              {{ preset.label }}
            </button>
            <button
              v-if="hasDateFilter"
              @click="clearDate"
              class="px-2.5 py-1.5 rounded-lg text-xs font-medium border border-red-200 text-red-500 hover:bg-red-50 transition-all"
            >
              Hapus
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Active Filter Chips -->
    <div v-if="activeChips.length > 0" class="flex flex-wrap gap-2 items-center">
      <span class="text-xs text-gray-400 font-medium">Filter aktif:</span>
      <TransitionGroup name="chip">
        <span
          v-for="chip in activeChips"
          :key="chip.key"
          class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200"
        >
          {{ chip.label }}
          <button
            @click="removeChip(chip)"
            class="text-indigo-400 hover:text-indigo-700 transition-colors leading-none"
          >✕</button>
        </span>
      </TransitionGroup>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useCategoryStore } from '@/stores/categoryStore'
import { useDebounce }      from '@/composables/useDebounce'

const props = defineProps({
  filters:   { type: Object, default: () => ({}) },
  sorting:   { type: Object, default: () => ({}) },
  isLoading: { type: Boolean, default: false },
})
const emit = defineEmits(['filter-change', 'reset'])

const categoryStore  = useCategoryStore()
const showDatePicker = ref(false)

// ─── Search with debounce ─────────────────────────────────────────────────────
const searchInput    = ref(props.filters.search ?? '')
const debouncedSearch = useDebounce(searchInput, 300)

watch(debouncedSearch, (val) => emit('filter-change', 'search', val))
watch(() => props.filters.search, (val) => {
  if (val === '' && searchInput.value !== '') searchInput.value = ''
})

function onSearch(val) { searchInput.value = val }
function clearSearch()  { searchInput.value = ''; emit('filter-change', 'search', '') }

// ─── Sort: gabungkan sort_by + sort_order jadi 1 dropdown ────────────────────
const sortValue = computed(() => `${props.sorting.sort_by}|${props.sorting.sort_order}`)

function onSortChange(val) {
  const [by, order] = val.split('|')
  emit('filter-change', 'sort_by', by)
  emit('filter-change', 'sort_order', order)
}

// ─── Date range ───────────────────────────────────────────────────────────────
const hasDateFilter = computed(() =>
  !!(props.filters.transaction_date_from || props.filters.transaction_date_to)
)

const dateRangeLabel = computed(() => {
  const from = props.filters.transaction_date_from
  const to   = props.filters.transaction_date_to
  if (!from && !to) return 'Rentang Tanggal'
  const fmt = (d) => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
  if (from && to)  return `${fmt(from)} — ${fmt(to)}`
  if (from)        return `Dari ${fmt(from)}`
  return `Sampai ${fmt(to)}`
})

function clearDate() {
  emit('filter-change', 'transaction_date_from', '')
  emit('filter-change', 'transaction_date_to', '')
  showDatePicker.value = false
}

// Quick date presets
const datePresets = computed(() => {
  const now   = new Date()
  const y     = now.getFullYear()
  const m     = now.getMonth()

  const pad   = (n) => String(n).padStart(2, '0')
  const fmt   = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`

  const thisMonthStart = new Date(y, m, 1)
  const thisMonthEnd   = new Date(y, m + 1, 0)
  const lastMonthStart = new Date(y, m - 1, 1)
  const lastMonthEnd   = new Date(y, m, 0)
  const last30         = new Date(now); last30.setDate(now.getDate() - 29)
  const last7          = new Date(now); last7.setDate(now.getDate() - 6)

  return [
    { label: '7 Hari',    from: fmt(last7),         to: fmt(now) },
    { label: '30 Hari',   from: fmt(last30),         to: fmt(now) },
    { label: 'Bulan Ini',   from: fmt(thisMonthStart), to: fmt(thisMonthEnd) },
    { label: 'Bulan Lalu',  from: fmt(lastMonthStart), to: fmt(lastMonthEnd) },
  ]
})

function applyPreset(preset) {
  emit('filter-change', 'transaction_date_from', preset.from)
  emit('filter-change', 'transaction_date_to',   preset.to)
}

// ─── Active filter chips ──────────────────────────────────────────────────────
const activeChips = computed(() => {
  const chips = []

  if (props.filters.search) {
    chips.push({ key: 'search', label: `"${props.filters.search}"`, clear: () => clearSearch() })
  }
  if (props.filters.type) {
    chips.push({
      key: 'type',
      label: props.filters.type === 'income' ? '📈 Pemasukan' : '📉 Pengeluaran',
      clear: () => emit('filter-change', 'type', ''),
    })
  }
  if (props.filters.category_id) {
    const cat = categoryStore.categories.find(c => String(c.id) === String(props.filters.category_id))
    chips.push({
      key: 'category',
      label: cat?.name ?? 'Kategori',
      clear: () => emit('filter-change', 'category_id', ''),
    })
  }
  if (hasDateFilter.value) {
    chips.push({ key: 'date', label: dateRangeLabel.value, clear: clearDate })
  }

  return chips
})

function removeChip(chip) { chip.clear() }

const hasActiveFilters = computed(() => activeChips.value.length > 0)
</script>

<style scoped>
.slide-down-enter-active, .slide-down-leave-active {
  transition: all 0.2s ease;
  overflow: hidden;
}
.slide-down-enter-from, .slide-down-leave-to {
  opacity: 0;
  max-height: 0;
  transform: translateY(-4px);
}
.slide-down-enter-to, .slide-down-leave-from {
  max-height: 200px;
}

.chip-enter-active, .chip-leave-active { transition: all 0.15s ease; }
.chip-enter-from, .chip-leave-to { opacity: 0; transform: scale(0.85); }
</style>
