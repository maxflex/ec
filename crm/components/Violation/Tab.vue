<script setup lang="ts">
import type { ViolationDialog } from '#components'
import type { ViolationListResource } from '.'
import { apiUrl } from '.'

const { clientId } = defineProps<{
  clientId?: number
}>()
const filters = useAvailableYearsFilter()
const dialog = ref<InstanceType<typeof ViolationDialog>>()
const { items, indexPageData } = useIndex<ViolationListResource>(
  apiUrl,
  filters,
  {
    staticFilters: {
      client_id: clientId,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <ViolationList :items="items" @edit="dialog?.edit" />
  </UiIndexPage>
  <ViolationDialog ref="dialog" v-model="items" />
</template>
