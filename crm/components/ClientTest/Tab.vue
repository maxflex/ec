<script setup lang="ts">
import type { TestSelectorDialog } from '#build/components'
import type { ClientTestResource } from '.'

const { clientId } = defineProps<{ clientId: number }>()

const { $addSseListener } = useNuxtApp()

const testSelectorDialog = ref<InstanceType<typeof TestSelectorDialog>>()
const filters = useAvailableYearsFilter()

const { items, indexPageData, availableYears, reloadData } = useIndex<ClientTestResource>(
  `client-tests`,
  filters,
  {
    loadAvailableYears: true,
    staticFilters: {
      client_id: clientId,
    },
  },
)

function onDestroy(clientTest: ClientTestResource) {
  if (!confirm(`Вы уверены, что хотите удалить тест ${clientTest.name}?`)) {
    return
  }
  const index = items.value.findIndex(t => t.id === clientTest.id)
  if (index !== -1) {
    items.value.splice(index, 1)
    useHttp(
      `client-tests/${clientTest.id}`,
      {
        method: 'delete',
      },
    )
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
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
    </template>
    <template #buttons>
      <v-btn
        color="primary"
        @click="() => testSelectorDialog?.open()"
      >
        добавить тесты
      </v-btn>
    </template>
    <ClientTestList :items="items" @destroy="onDestroy" @timeout="reloadData()" />
  </UiIndexPage>

  <TestSelectorDialog
    ref="testSelectorDialog"
    :client-id="clientId"
    @saved="reloadData()"
  />
</template>
