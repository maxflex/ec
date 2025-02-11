<script setup lang="ts">
const { user } = useAuthStore()
const availableYearsLoaded = ref(false)
const filters = ref<AvailableYearsFilter>({ })

const { items, indexPageData } = useIndex<QuartersGradesResource, AvailableYearsFilter>(
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
      <AvailableYearsSelector v-model="filters.year" mode="grades" :client-id="user!.id" @loaded="onAvailableYearsLoaded()" />
    </template>
    <GradeListForClients :items="items" />
  </UiIndexPage>
</template>
