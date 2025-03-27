<script setup lang="ts">
const { teacherId } = defineProps<{ teacherId: number }>()
const filters = useAvailableYearsFilter()

const { items, indexPageData, availableYears } = useIndex<GroupListResource>(
  `groups`,
  filters,
  {
    loadAvailableYears: true,
    staticFilters: {
      teacher_id: teacherId,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
    </template>
    <GroupList :items="items" />
  </UiIndexPage>
</template>
