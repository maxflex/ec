<script setup lang="ts">
const { teacherId } = defineProps<{ teacherId: number }>()
const filters = ref<AvailableYearsFilter>({
  year: undefined,
})
const { items, indexPageData, availableYears } = useIndex<GroupListResource, AvailableYearsFilter>(
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
      <AvailableYearsSelector2 v-model="filters.year" :items="availableYears" />
    </template>
    <GroupList :items="items" />
  </UiIndexPage>
</template>
