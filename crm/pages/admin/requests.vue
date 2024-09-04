<script setup lang="ts">
import type { RequestDialog } from '#build/components'
import type { Filters } from '~/components/Request/Filters.vue'

const { items, indexPageData, onFiltersApply } = useIndex<RequestListResource, Filters>(`requests`)

const requestDialog = ref<InstanceType<typeof RequestDialog>>()

function onRequestUpdated(r: RequestListResource) {
  const index = items.value.findIndex(e => e.id === r.id)
  if (index !== -1) {
    items.value[index] = r
  }
  else {
    items.value.unshift(r)
  }
  itemUpdated('request', r.id)
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <RequestFilters @apply="onFiltersApply" />
    </template>
    <template #buttons>
      <v-btn color="primary" @click="requestDialog?.create()">
        добавить заявку
      </v-btn>
    </template>
    <RequestList v-model="items" />
  </UiIndexPage>
  <RequestDialog
    ref="requestDialog"
    @updated="onRequestUpdated"
  />
</template>
