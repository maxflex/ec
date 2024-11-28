<script setup lang="ts">
const modeLabel = {
  clients: 'ученики',
  teachers: 'преподаватели',
} as const

type Mode = keyof typeof modeLabel

interface Filters {
  mode: Mode
  month: Month
}

interface Extra {
  mode: Mode
}

const filters = ref<Filters>({
  month: new Date().getMonth() + 1 as Month,
  mode: 'clients',
})

const { items, indexPageData, extra } = useIndex<ScholarshipScoreClient, Filters, Extra>(
  `scholarship-scores`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiMonthSelector v-model="filters.month" density="comfortable" />
      <v-select
        v-model="filters.mode"
        :items="selectItems(modeLabel)"
        density="comfortable"
        label="Отображение"
      />
    </template>
    <ScholarshipScoreClientsList v-if="extra.mode === 'clients'" :items="items" />
    <ScholarshipScoreTeachersList v-else :items="items" />
  </UiIndexPage>
</template>
