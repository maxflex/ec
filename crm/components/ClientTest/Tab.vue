<script setup lang="ts">
import type { TestSelectorDialog } from '#build/components'

const { clientId } = defineProps<{
  clientId: number
}>()

const items = ref<ClientTestResource[]>([])
const testSelectorDialog = ref<InstanceType<typeof TestSelectorDialog>>()
const year = ref<Year>(currentAcademicYear())
const loading = ref(false)

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

watch(year, loadData)

nextTick(loadData)
</script>

<template>
  <div class="client-test-tab">
    <div class="filters">
      <div class="filters-inputs">
        <v-select
          v-model="year"
          label="Учебный год"
          :items="selectItems(YearLabel)"
          density="comfortable"
        />
      </div>
      <v-btn
        color="primary"
        @click="() => testSelectorDialog?.open()"
      >
        добавить тест
      </v-btn>
    </div>
    <UiLoader v-if="loading" />
    <ClientTestList v-else :items="items" />
  </div>
  <TestSelectorDialog
    ref="testSelectorDialog"
    @selected="onTestsSelected"
  />
</template>
