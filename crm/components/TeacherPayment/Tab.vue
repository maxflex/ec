<script setup lang="ts">
import type { TeacherPaymentDialog } from '#build/components'

const { teacherId } = defineProps<{
  teacherId: number
}>()
const filters = ref<{
  year: Year
}>({
  year: currentAcademicYear(),
})
const loading = ref(false)
const items = ref<TeacherPaymentResource[]>([])
const teacherPaymentDialog = ref<InstanceType<typeof TeacherPaymentDialog>>()

async function loadData() {
  if (loading.value) {
    return
  }
  loading.value = true
  const { data } = await useHttp<ApiResponse<TeacherPaymentResource[]>>(
    'teacher-payments',
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

function onUpdated(p: TeacherPaymentResource) {
  const index = items.value.findIndex(e => e.id === p.id)
  if (index !== -1) {
    items.value[index] = p
  }
  else {
    items.value.unshift(p)
  }
  itemUpdated('teacher-payments', p.id)
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
      color="primary"
      @click="teacherPaymentDialog?.create(teacherId, filters.year)"
    >
      добавить платеж
    </v-btn>
  </div>
  <div>
    <UiLoader3 :loading="loading" />
    <TeacherPaymentList :items="items" @open="teacherPaymentDialog?.edit" />
  </div>
  <TeacherPaymentDialog ref="teacherPaymentDialog" @updated="onUpdated" />
</template>
