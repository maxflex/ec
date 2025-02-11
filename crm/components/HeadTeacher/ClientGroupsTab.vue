<script setup lang="ts">
const { clientId } = defineProps<{ clientId: number }>()

const filters = ref<AvailableYearsFilter>({})
const { items, indexPageData } = useIndex<GroupListResource, AvailableYearsFilter>(
  `groups`,
  filters,
  {
    instantLoad: false,
    staticFilters: {
      client_id: clientId,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector
        v-model="filters.year"
        :client-id="clientId"
        mode="groups"
      />
    </template>
    <GroupTeacherList :items="items" />
  </UiIndexPage>
</template>
