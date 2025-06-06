<script setup lang="ts">
import type { ClientListResource } from '~/components/Client'

const { teacherId } = defineProps<{ teacherId: number }>()

const filters = useAvailableYearsFilter()

const { items, indexPageData, availableYears } = useIndex<ClientListResource>(
  `clients`,
  filters,
  {
    loadAvailableYears: true,
    staticFilters: {
      head_teacher_id: teacherId,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
      <!-- <UiClearableSelect v-model="filters.year" label="Учебный год" :items="selectItems(YearLabel)" density="comfortable" /> -->
    </template>
    <ClientList :items="items" />
  </UiIndexPage>
</template>
