<script setup lang="ts">
import type { ExamScoreDialog } from '#build/components'

const { clientId } = defineProps<{
  clientId: number
}>()
const filters = ref<{
  year: Year
}>({
  year: currentAcademicYear(),
})
const loading = ref(true)
const items = ref<ExamScoreResource[]>([])
const examScoreDialog = ref<InstanceType<typeof ExamScoreDialog>>()

async function loadData() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<ExamScoreResource[]>>(
    'exam-scores',
    {
      params: {
        ...filters.value,
        client_id: clientId,
      },
    },
  )
  if (data.value) {
    const { data: newItems } = data.value
    items.value = newItems
  }
  loading.value = false
}

watch(filters.value, () => loadData())

function onUpdated(es: ExamScoreResource) {
  const index = items.value.findIndex(e => e.id === es.id)
  if (index !== -1) {
    items.value[index] = es
  }
  else {
    items.value.unshift(es)
  }
  itemUpdated('exam-score', es.id)
}

const noData = computed(() => !loading.value && items.value.length === 0)

nextTick(loadData)
</script>

<template>
  <UiIndexPage :data="{ loading, noData }">
    <template #filters>
      <v-select
        v-model="filters.year"
        :items="selectItems(YearLabel)"
        label="Год"
        density="comfortable"
      />
    </template>
    <template #buttons>
      <v-btn
        color="primary"
        @click="examScoreDialog?.create(clientId, filters.year)"
      >
        добавить баллы
      </v-btn>
    </template>
    <ExamScoreList :items="items" @edit="({ id }) => examScoreDialog?.edit(id)" />
  </UiIndexPage>

  <ExamScoreDialog ref="examScoreDialog" @updated="onUpdated" />
</template>
