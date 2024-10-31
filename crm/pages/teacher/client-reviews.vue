<script setup lang="ts">
interface Filters {
  type?: number
  program?: Program
}
const filters = ref<Filters>(loadFilters({}))
const { items, indexPageData } = useIndex<ClientReviewListResource, Filters>(
  `client-reviews`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiClearableSelect
        v-model="filters.type"
        label="Тип"
        :items="yesNo('созданные', 'требуется создание')"
        density="comfortable"
      />
      <UiClearableSelect
        v-model="filters.program"
        label="Программа"
        :items="selectItems(ProgramLabel)"
        density="comfortable"
      />
    </template>
    <ClientReviewList :items="items" />
  </UiIndexPage>
</template>
