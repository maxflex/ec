<script setup lang="ts">
import type { ContractVersionDialog } from '#build/components'

const contractVersionDialog = ref<InstanceType<typeof ContractVersionDialog>>()
const filters = ref<ContractVersionFilters>(loadFilters({
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<ContractVersionListResource, ContractVersionFilters>(
    `contract-versions`,
    filters,
)

function onUpdated(cv: ContractVersionListResource) {
  const index = items.value.findIndex(e => e.id === cv.id)
  if (index !== -1) {
    items.value[index] = cv
    itemUpdated('contract-version', cv.id)
  }
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <ContractVersionFilters v-model="filters" />
    </template>
    <ContractVersionList
      :items="items"
      @edit="contractVersionDialog?.edit"
    />
    <ContractVersionDialog
      ref="contractVersionDialog"
      @updated="onUpdated"
    />
  </UiIndexPage>
</template>
