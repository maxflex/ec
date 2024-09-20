<script setup lang="ts">
import type { TeacherPaymentDialog } from '#build/components'

const { teacherId } = defineProps<{ teacherId: number }>()

const tabName = 'TeacherPaymentTab'

const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}, tabName))
const teacherPaymentDialog = ref<InstanceType<typeof TeacherPaymentDialog>>()

const { items, indexPageData } = useIndex<TeacherPaymentResource, YearFilters>(
    `teacher-payments`,
    filters,
    {
      tabName,
      staticFilters: {
        teacher_id: teacherId,
      },
    },
)

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
</script>

<template>
  <UiIndexPage :data="indexPageData">
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
        @click="teacherPaymentDialog?.create(teacherId, filters.year)"
      >
        добавить платеж
      </v-btn>
    </template>
    <TeacherPaymentList :items="items" @open="teacherPaymentDialog?.edit" />
  </UiIndexPage>
  <TeacherPaymentDialog ref="teacherPaymentDialog" @updated="onUpdated" />
</template>
