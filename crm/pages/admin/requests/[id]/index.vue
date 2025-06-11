<script setup lang="ts">
import type { RequestDialog } from '#components'
import type { RequestListResource } from '~/components/Request'

const route = useRoute()
const id = Number.parseInt(route.params.id as string)
const filters = ref({
  id,
})

const { items, indexPageData } = useIndex<RequestListResource>(
  `requests`,
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
      <v-text-field
        density="comfortable"
        :model-value="id"
        label="ID"
        disabled
      />
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
