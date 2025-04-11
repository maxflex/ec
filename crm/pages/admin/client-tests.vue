<script setup lang="ts">
import type { ClientTestResource } from '~/components/ClientTest'

const { $addSseListener } = useNuxtApp()

interface ClientTestFilters {
  year: Year
  program?: Program
  status?: ClientTestStatus
}

const filters = ref<ClientTestFilters>(loadFilters({
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<ClientTestResource>(
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

$addSseListener('ClientTestUpdatedEvent', (clientTest: ClientTestResource) => {
  const index = items.value.findIndex(t => t.id === clientTest.id)

  if (index !== -1) {
    items.value.splice(index, 1, clientTest)
    itemUpdated('client-test', clientTest.id)
  }
})
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <ClientTestFilters v-model="filters" />
    </template>
    <ClientTestList :items="items" show-client @destroy="onDestroy" />
  </UiIndexPage>
</template>
