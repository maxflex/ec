<script setup lang="ts">
import { mdiAccountGroup } from '@mdi/js'
import type { LessonConductDialog, LessonDialog } from '#build/components'

const { entity, id, editable, conductable } = defineProps<{
  entity: Extract<EntityString, 'client' | 'teacher' | 'group'>
  id: number
  editable?: boolean
  conductable?: boolean
}>()
const year = ref<Year>(2023)
const loading = ref(false)
const lessons = ref<LessonListResource[]>([])
const lessonDialog = ref<InstanceType<typeof LessonDialog>>()
const conductDialog = ref<InstanceType<typeof LessonConductDialog>>()

async function loadData() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<LessonListResource[]>>('lessons', {
    params: {
      [`${entity}_id`]: id,
      year: entity === 'group' ? undefined : year.value,
    },
  })
  if (data.value) {
    lessons.value = data.value.data
  }
  loading.value = false
}

function onLessonUpdated(l: LessonListResource) {
  const index = lessons.value.findIndex(e => e.id === l.id)
  if (index !== -1) {
    lessons.value[index] = l
  }
  else {
    lessons.value.push(l)
    smoothScroll('main', 'bottom')
  }
}

function onLessonDestroyed(l: LessonListResource) {
  const index = lessons.value.findIndex(e => e.id === l.id)
  if (index !== -1) {
    lessons.value.splice(index, 1)
  }
}

watch(year, loadData)

nextTick(loadData)
</script>

<template>
  <div v-if="entity !== 'group'" class="filters">
    <div class="filters-inputs" style="justify-content: space-between; align-items: center; width: 100%">
      <div>
        <v-select
          v-model="year"
          label="Учебный год"
          :items="selectItems(YearLabel)"
          density="comfortable"
        />
      </div>
    </div>
  </div>
  <UiLoaderr v-if="loading" />
  <div v-else class="table table--actions-on-hover lessons">
    <div
      v-for="(l, i) in lessons"
      :key="l.id"
      style="padding-right: 10px !important"
    >
      <div v-if="editable || conductable" class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          color="gray"
          @click="editable ? lessonDialog?.edit(l.id) : conductDialog?.open(l.id, l.status)"
        />
      </div>
      <div
        class="text-gray"
        style="width: 20px"
      >
        {{ i + 1 }}
      </div>
      <div style="width: 100px">
        {{ formatDate(l.start_at) }}
      </div>
      <div style="width: 120px">
        {{ formatTime(l.start_at) }} – {{ l.time_end }}
      </div>
      <div style="width: 80px">
        К–{{ l.cabinet }}
      </div>
      <div style="width: 150px">
        <NuxtLink
          :to="{ name: 'teachers-id', params: { id: l.teacher.id } }"
        >
          {{ formatNameShort(l.teacher) }}
        </NuxtLink>
      </div>
      <div style="width: 90px">
        <NuxtLink :to="{ name: 'groups-id', params: { id: l.group.id } }">
          ГР-{{ l.group.id }}
        </NuxtLink>
      </div>
      <div style="width: 120px">
        {{ ProgramShortLabel[l.group.program] }}
      </div>
      <div style="width: 80px; display: flex; align-items: center;">
        <v-icon :icon="mdiAccountGroup" class="mr-2 vfn-1" />
        {{ l.group.contracts_count }}
      </div>
      <div style="width: 140px">
        <LessonStatus2 :status="l.status" />
      </div>
      <div>
        <v-chip v-if="l.is_first" class="text-deepOrange">
          первое
        </v-chip>
        <v-chip v-else-if="l.is_unplanned" class="text-purple">
          внеплановое
        </v-chip>
      </div>
    </div>
    <div
      v-if="editable"
      style="border: none"
    >
      <a
        class="cursor-pointer"
        @click="() => lessonDialog?.create(id)"
      >
        добавить занятие
      </a>
    </div>
  </div>
  <LessonDialog
    v-if="editable"
    ref="lessonDialog"
    @updated="onLessonUpdated"
    @destroyed="onLessonDestroyed"
  />
  <LessonConductDialog
    v-else-if="conductable"
    ref="conductDialog"
    @updated="loadData()"
  />
</template>
