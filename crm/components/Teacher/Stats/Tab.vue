<script setup lang="ts">
import type { TeacherResource } from '~/components/Teacher'
import type { TeacherStats, TeacherStatsMode } from '~/components/Teacher/Stats'
import { apiUrl, TeacherStatsModeLabel } from '~/components/Teacher/Stats'

interface Filters {
  year: Year | null
  mode: TeacherStatsMode
  direction: Direction[]
}

const { teacher } = defineProps<{
  teacher: TeacherResource
}>()

const mode = ref<TeacherStatsMode>('day')

const filters = ref<Filters>({
  year: null,
  mode: 'day',
  direction: [],
})

const availableYears = ref<Year[]>()
const loading = ref(true)
const isExpanded = ref(false)

let panel: HTMLElement = {} as HTMLElement

const stats = ref<TeacherStats>()

async function loadData() {
  loading.value = true
  const { data } = await useHttp<TeacherStats>(apiUrl, {
    params: {
      teacher_id: teacher.id,
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
      teacher_id: teacher.id,
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

function toggleExpand() {
  panel.style.display = isExpanded.value ? 'block' : 'none'
  isExpanded.value = !isExpanded.value
}

watch(filters, loadData, { deep: true })

onMounted(() => {
  panel = document.documentElement.querySelector('.panel') as HTMLElement
  const filtersEl = document.documentElement.querySelector('.teacher-stats__filters') as HTMLElement
  filtersEl.style.top = `${panel.clientHeight}px`

  setTimeout(() => {
    const header = document.documentElement.querySelector('.teacher-stats-table__header') as HTMLElement
    header.style.top = `${filtersEl.clientHeight + filtersEl.offsetTop + 1}`
  }, 200)
})

nextTick(loadAvailableYears)
</script>

<template>
  <UiIndexPage
    class="teacher-stats"
    :class="{ 'teacher-stats--expanded': isExpanded }"
    :data="{ loading, noData: stats === undefined }"
  >
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
      <v-btn
        icon="$collapse" color="primary"
        :size="48"
        style="transition: all ease-in-out 0.2s"
        @click="toggleExpand()"
      />
    </template>
    <TeacherStatsTable :stats="stats!" :mode="mode" />
  </UiIndexPage>
</template>

<style lang="scss">
.teacher-stats {
  &__filters {
    position: sticky;
    top: 230px;
    left: 0;
    z-index: 1;
  }

  &--expanded {
    &__filters {
      position: sticky;
      top: 0 !important;
      left: 0;
      z-index: 1;
      button {
        transform: rotate(180deg);
      }
    }
    .teacher-stats-table__header {
      top: 81px !important;
    }
  }
}
</style>
