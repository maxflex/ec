<script setup lang="ts">
import type { EventDialog, EventParticipantsDialog } from '#components'
import type { Filters } from '~/components/Event/Filters.vue'

const loading = ref(false)
const items = ref<EventListResource[]>([])
let filters: Filters = {
  year: currentAcademicYear(),
}
const eventDialog = ref<InstanceType<typeof EventDialog>>()
const eventParticipantsDialog = ref<InstanceType<typeof EventParticipantsDialog>>
let scrollContainer: HTMLElement | null = null

async function loadData() {
  if (loading.value) {
    return
  }
  loading.value = true
  const { data } = await useHttp<ApiResponse<EventListResource[]>>(
    'events',
    {
      params: {
        ...filters,
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
  filters = f
  if (scrollContainer) {
    scrollContainer.scrollTop = 0
  }
  loadData()
}

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
  <div class="filters">
    <EventFilters :filters="filters" @apply="onFiltersApply" />
    <v-btn
      append-icon="$next"
      color="primary"
      @click="eventDialog?.create(filters.year)"
    >
      добавить событие
    </v-btn>
  </div>
  <div>
    <UiLoader3 :loading="loading" />
    <EventList
      :items="items"
      :year="filters.year"
      @edit="eventDialog?.edit"
      @participants="eventParticipantsDialog?.open"
    />
  </div>
  <EventDialog ref="eventDialog" @updated="onUpdated" @deleted="onDeleted" />
  <EventParticipantsDialog ref="eventParticipantsDialog" />
</template>
