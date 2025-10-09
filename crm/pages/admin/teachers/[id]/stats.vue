<script setup lang="ts">
import type { TeacherResource } from '~/components/Teacher'
import type { TeacherStats, TeacherStatsMode } from '~/components/Teacher/Stats'
import { apiUrl, TeacherStatsModeLabel } from '~/components/Teacher/Stats'

interface Filters {
  year: Year | null
  mode: TeacherStatsMode
  direction: Direction[]
}

const mode = ref<TeacherStatsMode>('day')
const route = useRoute()
const teacherId = Number.parseInt(route.params.id as string)
const teacher = ref<TeacherResource>()

const filters = ref<Filters>({
  year: null,
  mode: 'day',
  direction: [],
})

const availableYears = ref<Year[]>()
const loading = ref(true)

const stats = ref<TeacherStats>()

async function loadData() {
  loading.value = true
  const { data } = await useHttp<TeacherStats>(apiUrl, {
    params: {
      teacher_id: teacherId,
      ...transformArrayKeys(filters.value),
    },
  })
  stats.value = data.value!
  mode.value = filters.value.mode
  loading.value = false
}

async function loadAvailableYears() {
  const { data } = await useHttp<Year[]>(apiUrl, {
    params: {
      teacher_id: teacherId,
      available_years: 1,
    },
  })
  availableYears.value = data.value!
  if (availableYears.value.length === 0) {
    loading.value = false
    return
  }

  filters.value.year = availableYears.value[0]
}

async function loadTeacher() {
  const { data } = await useHttp<TeacherResource>(`teachers/${teacherId}`)
  teacher.value = data.value!
}

watch(filters, loadData, { deep: true })

nextTick(loadTeacher)
nextTick(loadAvailableYears)
</script>

<template>
  <UiIndexPage class="teacher-stats-new" :data="{ loading, noData: stats === undefined }">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
      <v-select
        v-model="filters.mode"
        :items="selectItems(TeacherStatsModeLabel)"
        label="Группировка"
        density="comfortable"
      />
      <UiMultipleSelect
        v-model="filters.direction"
        label="Направление"
        :items="selectItems(DirectionLabel)"
        density="comfortable"
      />
    </template>
    <template #buttons>
      <v-btn v-if="teacher" color="primary" :to="{ name: 'teachers-id', params: { id: teacherId } }">
        <template #prepend>
          <v-icon icon="$back" />
        </template>
        {{ formatName(teacher, 'initials') }}
      </v-btn>
    </template>
    <TeacherStatsTable :stats="stats!" :mode="mode" />
  </UiIndexPage>
</template>

<style lang="scss">
.page-teachers-id-stats {
  overflow: hidden;
}
</style>
