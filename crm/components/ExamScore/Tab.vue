<script setup lang="ts">
import type { ExamScoreDialog } from '#build/components'

const { clientId } = defineProps<{ clientId: number }>()
const examScoreDialog = ref<InstanceType<typeof ExamScoreDialog>>()
const filters = ref<AvailableYearsFilter>({
  year: undefined,
})

const { items, indexPageData, availableYears } = useIndex<ExamScoreResource, AvailableYearsFilter>(
  `exam-scores`,
  filters,
  {
    loadAvailableYears: true,
    staticFilters: {
      client_id: clientId,
    },
  },
)

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
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector2 v-model="filters.year" :items="availableYears" />
    </template>
    <template #buttons>
      <v-btn
        color="primary"
        @click="examScoreDialog?.create(clientId)"
      >
        добавить баллы
      </v-btn>
    </template>
    <ExamScoreList :items="items" @edit="({ id }) => examScoreDialog?.edit(id)" />
  </UiIndexPage>

  <ExamScoreDialog ref="examScoreDialog" @updated="onUpdated" />
</template>
