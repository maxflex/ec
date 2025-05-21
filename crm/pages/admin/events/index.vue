<script setup lang="ts">
import type { EventDialog } from '#components'

const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}))
const eventDialog = ref<InstanceType<typeof EventDialog>>()
const { items, indexPageData } = useIndex<EventListResource>(`events`, filters)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <v-select
        v-model="filters.year"
        label="Учебный год"
        :items="selectItems(YearLabel)"
        density="comfortable"
      />
    </template>
    <template #buttons>
      <v-btn
        append-icon="$next"
        color="primary"
        @click="eventDialog?.create(filters.year)"
      >
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
