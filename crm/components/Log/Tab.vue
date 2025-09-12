<script setup lang="ts">
const { clientId } = defineProps<{
  clientId: number
}>()

const tabName = 'LogsTab'

const filters = ref<{ entity_type?: EntityType }>({})

const { items, indexPageData } = useIndex<LogResource>(
  `logs`,
  filters,
  {
    tabName,
    staticFilters: {
      client_id: clientId,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiClearableSelect
        v-model="filters.entity_type"
        label="Пользователь"
        :items="selectItems(EntityTypeLabel, ['App\\Models\\Client', 'App\\Models\\Representative'])"
        density="comfortable"
      />
    </template>
    <LogList :items="items" />
  </UiIndexPage>
</template>
