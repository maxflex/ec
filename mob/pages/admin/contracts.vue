<script setup lang="ts">
import type { ContractFilters } from '~/components/Contract/Filters.vue'

const filters = ref<ContractFilters>(loadFilters({
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<ContractVersionListResource>(
  `contract-versions`,
  filters,
)
</script>

<template>
  <UiPageTitle>
    Договоры
  </UiPageTitle>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <ContractFilters v-model="filters" />
    </template>
    <ContractList :items="items" />
  </UiIndexPage>
</template>
