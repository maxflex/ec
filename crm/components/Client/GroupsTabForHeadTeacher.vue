<script setup lang="ts">
const { clientId } = defineProps<{ clientId: number }>()
const tabName = 'GroupsTabForHeadTeacher'

const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}, tabName))

const { items, indexPageData } = useIndex<GroupListResource, GroupFilters>(
  `groups`,
  filters,
  {
    tabName,
    staticFilters: {
      client_id: clientId,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiYearSelector v-model="filters.year" density="comfortable" />
    </template>
    <GroupList :items="items" />
  </UiIndexPage>
</template>
