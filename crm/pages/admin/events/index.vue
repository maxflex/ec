<script setup lang="ts">
import type { EventDialog } from '#components'
import type { EventListResource } from '~/components/Event'

const filters = useAvailableYearsFilter()
const eventDialog = ref<InstanceType<typeof EventDialog>>()
const { items, indexPageData, availableYears } = useIndex<EventListResource>(`events`, filters, {
  loadAvailableYears: true,
})
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
    </template>
    <template #buttons>
      <v-btn color="primary" @click="eventDialog?.create()">
        добавить событие
      </v-btn>
    </template>
    <EventList
      :items="items"
      :year="filters.year"
      @edit="eventDialog?.edit"
    />
  </UiIndexPage>
  <LazyEventDialog ref="eventDialog" />
</template>
