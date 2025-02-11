<script setup lang="ts">
import type { TeacherServiceDialog } from '#build/components'

const { teacherId } = defineProps<{ teacherId: number }>()
const teacherServiceDialog = ref<InstanceType<typeof TeacherServiceDialog>>()
const filters = ref<AvailableYearsFilter>({ })
const availableYearsLoaded = ref(false)
const { items, indexPageData } = useIndex<TeacherServiceResource, AvailableYearsFilter>(
  `teacher-services`,
  filters,
  {
    instantLoad: false,
    staticFilters: {
      teacher_id: teacherId,
    },
  },
)

function onUpdated(p: TeacherServiceResource) {
  const index = items.value.findIndex(e => e.id === p.id)
  if (index !== -1) {
    items.value[index] = p
  }
  else {
    items.value.unshift(p)
  }
  itemUpdated('teacher-services', p.id)
}

const noData = computed(() => availableYearsLoaded.value && !filters.value.year)

function onAvailableYearsLoaded() {
  availableYearsLoaded.value = true
}
</script>

<template>
  <UiIndexPage :data="availableYearsLoaded && noData ? { noData, loading: false } : indexPageData">
    <template #filters>
      <AvailableYearsSelector
        v-model="filters.year"
        mode="teacher-services"
        :teacher-id="teacherId"
        @loaded="onAvailableYearsLoaded()"
      />
    </template>
    <template #buttons>
      <v-btn
        color="primary"
        @click="teacherServiceDialog?.create(teacherId)"
      >
        добавить допуслугу
      </v-btn>
    </template>
    <TeacherServiceList :items="items" @open="teacherServiceDialog?.edit" />
  </UiIndexPage>
  <TeacherServiceDialog ref="teacherServiceDialog" @updated="onUpdated" />
</template>
