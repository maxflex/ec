<script setup lang="ts">
const { clientId } = defineProps<{
  clientId?: number
}>()

const tabName = 'LogsTab'

const { items, indexPageData } = useIndex<LogResource>(
  `logs`,
  ref({}),
  {
    tabName,
    staticFilters: {
      entity_type: EntityTypeValue.client,
      entity_id: clientId,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <LogList :items="items" />
  </UiIndexPage>
</template>
