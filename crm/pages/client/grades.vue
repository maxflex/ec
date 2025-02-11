<script setup lang="ts">
interface Filters {
  year?: Year
}
const { user } = useAuthStore()
const availableYearsLoaded = ref(false)
const filters = ref<Filters>({
  year: undefined,
})

const { items, indexPageData } = useIndex<QuartersGradesResource, Filters>(
  `grades`,
  filters,
  {
    instantLoad: false,
  },
)

function onAvailableYearsLoaded() {
  availableYearsLoaded.value = true
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <YearSelector v-model="filters.year" mode="grades" :client-id="user!.id" @loaded="onAvailableYearsLoaded()" />
    </template>
    <GradeListForClients :items="items" />
  </UiIndexPage>
</template>
