<script setup lang="ts">
import type { GroupListResource } from '../Group'

const { clientId } = defineProps<{ clientId: number }>()

const filters = useAvailableYearsFilter()

const { items, indexPageData, availableYears } = useIndex<GroupListResource>(
  `groups`,
  filters,
  {
    loadAvailableYears: true,
    staticFilters: {
      client_id: clientId,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
    </template>
    <GroupTeacherList :items="items" />
  </UiIndexPage>
</template>
