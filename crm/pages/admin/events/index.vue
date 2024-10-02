<script setup lang="ts">
import type { EventDialog } from '#components'

interface Filters {
  year: Year
}

const loading = ref(false)
const items = ref<EventListResource[]>([])
const filters = ref<Filters>(loadFilters({
  year: currentAcademicYear(),
}))
const eventDialog = ref<InstanceType<typeof EventDialog>>()
let scrollContainer: HTMLElement | null = null

async function loadData() {
  if (loading.value) {
    return
  }
  loading.value = true
  const { data } = await useHttp<ApiResponse<EventListResource[]>>(
    `common/events`,
    {
      params: {
        ...filters.value,
      },
    },
  )
  if (data.value) {
    const { data: newItems } = data.value
    items.value = newItems
  }
  loading.value = false
}

function onFiltersApply(f: Filters) {
  filters.value = f
  if (scrollContainer) {
    scrollContainer.scrollTop = 0
  }
  loadData()
}

watch(filters, (newVal) => {
  saveFilters(newVal)
  onFiltersApply(newVal)
}, { deep: true })

function onUpdated(e: EventListResource) {
  const index = items.value.findIndex(x => x.id === e.id)
  if (index !== -1) {
    items.value[index] = e
  }
  else {
    items.value.unshift(e)
  }
  itemUpdated('event', e.id)
}

function onDeleted(e: EventResource) {
  const index = items.value.findIndex(x => x.id === e.id)
  if (index !== -1) {
    items.value.splice(index, 1)
  }
}

onMounted(() => {
  scrollContainer = document.documentElement.querySelector('main')
})

nextTick(loadData)
</script>

<template>
  <UiFilters>
    <v-select
      v-model="filters.year"
      label="Учебный год"
      :items="selectItems(YearLabel)"
      density="comfortable"
    />
    <template #buttons>
      <v-btn
        append-icon="$next"
        color="primary"
        @click="eventDialog?.create(filters.year)"
      >
        добавить событие
      </v-btn>
    </template>
  </UiFilters>
  <div>
    <UiLoader3 :loading="loading" />
    <EventList
      :items="items"
      :year="filters.year"
      @edit="eventDialog?.edit"
    />
  </div>
  <EventDialog ref="eventDialog" @updated="onUpdated" @deleted="onDeleted" />
</template>
