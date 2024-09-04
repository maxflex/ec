<script setup lang="ts">
import type { TestSelectorDialog } from '#build/components'

const { clientId } = defineProps<{
  clientId: number
}>()

const items = ref<ClientTestResource[]>([])
const testSelectorDialog = ref<InstanceType<typeof TestSelectorDialog>>()
const year = ref<Year>(currentAcademicYear())
const loading = ref(true)

async function loadData() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<ClientTestResource[]>>(
    `client-tests`,
    {
      params: {
        year: year.value,
        client_id: clientId,
      },
    },
  )
  if (data.value) {
    items.value = data.value.data
  }
  loading.value = false
}

async function onTestsSelected(tests: TestResource[]) {
  const { data } = await useHttp<ClientTestResource[]>(
    `client-tests`,
    {
      method: 'post',
      body: {
        ids: tests.map(t => t.id),
        year: year.value,
        client_id: clientId,
      },
    },
  )
  if (data.value) {
    for (const clientTest of data.value) {
      items.value.unshift(clientTest)
      itemUpdated('client-test', clientTest.id)
    }
  }
}

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

const noData = computed(() => !loading.value && items.value.length === 0)

watch(year, loadData)

nextTick(loadData)
</script>

<template>
  <UiIndexPage :data="{ loading, noData }">
    <template #filters>
      <v-select
        v-model="year"
        label="Учебный год"
        :items="selectItems(YearLabel)"
        density="comfortable"
      />
    </template>
    <template #buttons>
      <v-btn
        color="primary"
        @click="() => testSelectorDialog?.open()"
      >
        добавить тест
      </v-btn>
    </template>
    <ClientTestList :items="items" @destroy="onDestroy" />
  </UiIndexPage>

  <TestSelectorDialog
    ref="testSelectorDialog"
    @selected="onTestsSelected"
  />
</template>
