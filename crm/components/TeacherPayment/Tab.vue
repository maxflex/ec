<script setup lang="ts">
import type { TeacherPaymentDialog } from '#build/components'
import { apiUrl, type TeacherPaymentResource } from '.'

const { teacherId } = defineProps<{ teacherId: number }>()

const filters = ref<AvailableYearsFilter>({
  year: undefined,
})

const teacherPaymentDialog = ref<InstanceType<typeof TeacherPaymentDialog>>()

const { items, availableYears, indexPageData } = useIndex<TeacherPaymentResource, AvailableYearsFilter>(
  apiUrl,
  filters,
  {
    loadAvailableYears: true,
    staticFilters: {
      teacher_id: teacherId,
    },
  },
)

function onDeleted(p: TeacherPaymentResource) {
  const index = items.value.findIndex(e => e.id === p.id)
  items.value.splice(index, 1)
}

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
      <AvailableYearsSelector2
        v-model="filters.year"
        :items="availableYears"
      />
    </template>
    <template #buttons>
      <v-btn
        color="primary"
        @click="teacherPaymentDialog?.create(teacherId)"
      >
        добавить платеж
      </v-btn>
    </template>
    <TeacherPaymentList :items="items" @open="teacherPaymentDialog?.edit" />
  </UiIndexPage>
  <TeacherPaymentDialog ref="teacherPaymentDialog" @updated="onUpdated" @deleted="onDeleted" />
</template>
