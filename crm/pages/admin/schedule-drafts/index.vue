<script setup lang="ts">
import type { SavedScheduleDraftResource } from '~/components/ScheduleDraft'
import { apiUrl } from '~/components/ScheduleDraft'

const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<SavedScheduleDraftResource>(
  apiUrl,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiYearSelector v-model="filters.year" density="comfortable" />
    </template>
    <ScheduleDraftList :items="items" />
  </UiIndexPage>
</template>
