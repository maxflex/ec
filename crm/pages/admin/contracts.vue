<script setup lang="ts">
import type { ClientDialog, ContractVersionDialog } from '#build/components'
import type { ClientListResource } from '~/components/Client'
import type { ContractVersionListResource } from '~/components/ContractVersion'
import type { ContractVersionFilters } from '~/components/ContractVersion/Filters.vue'

const contractVersionDialog = ref<InstanceType<typeof ContractVersionDialog>>()
const clientDialog = ref<InstanceType<typeof ClientDialog>>()

const filters = ref<ContractVersionFilters>(loadFilters({
  year: currentAcademicYear(),
  direction: [],
}))

const { items, indexPageData } = useIndex<ContractVersionListResource>(
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

    <div class="table table--padding flex-start">
      <ContractVersionItem
        v-for="item in items"
        :key="item.id"
        :item="item"
        @edit="contractVersionDialog?.edit"
      />
    </div>
    <ContractVersionDialog ref="contractVersionDialog" @updated="onUpdated" />
  </UiIndexPage>
</template>

<style lang="scss">
.page-contracts {
  .filters__inputs {
    & > div {
      max-width: 220px !important;
    }
  }
}
</style>
