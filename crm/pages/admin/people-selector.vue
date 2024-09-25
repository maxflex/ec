<script setup lang="ts">
const filters = ref<PeopleSelectorFilters>({
  mode: 'clients',
  year: 2023,
  program: [],
  status: [],
})

const { items, meta, indexPageData } = useIndex<PersonListResource, PeopleSelectorFilters>(
    `people-selector`,
    filters,
)
</script>

<template>
  <UiIndexPage class="people-selector" :data="indexPageData">
    <template #filters>
      <PeopleSelectorFilters v-model="filters" />
    </template>
    <PeopleSelectorList :items="items" :meta="meta!" />
  </UiIndexPage>
</template>

<style lang="scss">
.people-selector {
  &__filters {
    --height: auto !important;
    padding-bottom: 24px !important;
    .filters__inputs {
      width: 100%;
      & > div {
        flex: 1;
        width: auto !important;
      }
    }
  }
}
</style>
