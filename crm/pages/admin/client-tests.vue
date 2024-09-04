<script setup lang="ts">
import type { Filters } from '~/components/ClientTest/Filters.vue'

const { items, loading, onFiltersApply } = useIndex<ClientTestResource, Filters>(`client-tests`)

function onDestroy(clientTest: ClientTestResource) {
  if (!confirm(`Вы уверены, что хотите удалить тест ${clientTest.name}?`)) {
    return
  }
  const index = items.value.findIndex(t => t.id === clientTest.id)
  if (index !== -1) {
    items.value.splice(index, 1)
    useHttp(`client-tests/${clientTest.id}`, {
      method: 'delete',
    })
  }
}
</script>

<template>
  <UiFilters>
    <ClientTestFilters @apply="onFiltersApply" />
  </UiFilters>
  <div>
    <UiLoader3 :loading="loading" />
    <ClientTestList :items="items" @destroy="onDestroy" />
  </div>
</template>
