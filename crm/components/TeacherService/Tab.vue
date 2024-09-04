<script setup lang="ts">
import type { TeacherServiceDialog } from '#build/components'

const { teacherId } = defineProps<{
  teacherId: number
}>()
const filters = ref<{
  year: Year
}>({
  year: currentAcademicYear(),
})
const loading = ref(false)
const items = ref<TeacherServiceResource[]>([])
const teacherServiceDialog = ref<InstanceType<typeof TeacherServiceDialog>>()

async function loadData() {
  if (loading.value) {
    return
  }
  loading.value = true
  const { data } = await useHttp<ApiResponse<TeacherServiceResource[]>>(
    'teacher-services',
    {
      params: {
        ...filters.value,
        teacher_id: teacherId,
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

nextTick(loadData)
</script>

<template>
  <UiFilters>
    <v-select
      v-model="filters.year" :items="selectItems(YearLabel)" label="Год"
      density="comfortable"
    />
    <template #buttons>
      <v-btn
        color="primary"
        @click="teacherServiceDialog?.create(teacherId, filters.year)"
      >
        добавить платеж
      </v-btn>
    </template>
  </UiFilters>
  <div>
    <UiLoader3 :loading="loading" />
    <TeacherServiceList :items="items" @open="teacherServiceDialog?.edit" />
  </div>
  <TeacherServiceDialog ref="teacherServiceDialog" @updated="onUpdated" />
</template>
