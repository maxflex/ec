<script setup lang="ts">
const { clientId } = defineProps<{ clientId: number }>()

const filters = ref<AvailableYearsFilter>({
  year: undefined,
})

const { items, indexPageData, availableYears } = useIndex<GroupListResource, AvailableYearsFilter>(
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
      <AvailableYearsSelector2 v-model="filters.year" :items="availableYears" />
    </template>
    <GroupTeacherList :items="items" />
  </UiIndexPage>
</template>
