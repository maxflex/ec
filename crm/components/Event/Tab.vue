<script setup lang="ts">
import type { EventDialog } from '#components'

const { clientId, teacherId } = defineProps<{
  clientId?: number
  teacherId?: number
}>()

const eventDialog = ref<InstanceType<typeof EventDialog>>()

const { indexPageData, items } = useIndex<EventListResource>(
  `events`,
  ref({}),
  {
    staticFilters: {
      client_id: clientId,
      teacher_id: teacherId,
    },
  },
)

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
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <EventList :items="items" @edit="eventDialog?.edit" />
  </UiIndexPage>
  <EventDialog ref="eventDialog" @updated="onUpdated" @deleted="onDeleted" />
</template>
