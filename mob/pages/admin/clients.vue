<script setup lang="ts">
const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<ClientListResource, YearFilters>(
  `clients`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <ClientFilters v-model="filters" />
    </template>
    <ClientList :items="items" />
  </UiIndexPage>
</template>
