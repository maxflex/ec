<script setup lang="ts">
const { clientId } = defineProps<{ clientId: number }>()

const availableYearsLoaded = ref(false)
const filters = ref<AvailableYearsFilter>({})
const noData = computed(() => availableYearsLoaded.value && !filters.value.year)
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
  <UiIndexPage :data="availableYearsLoaded && noData ? { noData, loading: false } : indexPageData">
    <template #filters>
      <AvailableYearsSelector
        v-model="filters.year"
        :client-id="clientId"
        mode="groups"
        @loaded="availableYearsLoaded = true"
      />
    </template>
    <GroupTeacherList :items="items" />
  </UiIndexPage>
</template>
