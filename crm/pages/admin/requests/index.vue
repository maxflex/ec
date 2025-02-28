<script setup lang="ts">
import type { RequestDialog } from '#components'
import { apiUrl, type RequestListResource } from '~/components/Request'
import type { Filters } from '~/components/Request/Filters.vue'

const filters = ref<Filters>(loadFilters({
  direction: [],
}))

const { items, indexPageData } = useIndex<RequestListResource, Filters>(
  apiUrl,
  filters,
)

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
      <RequestFilters v-model="filters" />
    </template>
    <template #buttons>
      <v-btn color="primary" @click="requestDialog?.create()">
        добавить заявку
      </v-btn>
    </template>
    <RequestList v-model="items" />
  </UiIndexPage>
  <RequestDialog ref="requestDialog" @updated="onRequestUpdated" />
</template>
