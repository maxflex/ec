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
const loading = ref(false)
const items = ref<ExamScoreResource[]>([])
const examScoreDialog = ref<InstanceType<typeof ExamScoreDialog>>()

async function loadData() {
  if (loading.value) {
    return
  }
  loading.value = true
  const { data } = await useHttp<ApiResponse<ExamScoreResource[]>>(
    'exam-scores',
    {
      params: {
        ...filters,
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

watch(filters, () => loadData())

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

nextTick(loadData)
</script>

<template>
  <div class="filters">
    <div class="filters-inputs">
      <v-select
        v-model="filters.year" :items="selectItems(YearLabel)" label="Год"
        density="comfortable"
      />
    </div>
    <v-btn
      append-icon="$next"
      color="primary"
      @click="examScoreDialog?.create(clientId, filters.year)"
    >
      добавить
    </v-btn>
  </div>
  <div>
    <UiLoader3 :loading="loading" />
    <ExamScoreList :items="items" @edit="examScoreDialog?.edit" />
  </div>
  <ExamScoreDialog ref="examScoreDialog" @updated="onUpdated" />
</template>
