<script setup lang="ts">
const filters = ref<ClientTestFilters>(loadFilters({
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<ClientTestResource, ClientTestFilters>(
    `client-tests`,
    filters,
)

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
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <ClientTestFilters v-model="filters" />
    </template>
    <ClientTestList :items="items" @destroy="onDestroy" />
  </UiIndexPage>
</template>
