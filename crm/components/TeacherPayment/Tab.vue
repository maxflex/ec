<script setup lang="ts">
import type { TeacherPaymentDialog } from '#build/components'

const { teacherId } = defineProps<{ teacherId: number }>()

const filters = ref<AvailableYearsFilter>({})
const teacherPaymentDialog = ref<InstanceType<typeof TeacherPaymentDialog>>()
const availableYearsLoaded = ref(false)

const { items, indexPageData } = useIndex<TeacherPaymentResource, AvailableYearsFilter>(
  `teacher-payments`,
  filters,
  {
    instantLoad: false,
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
        mode="teacher-payments"
        :teacher-id="teacherId"
        @loaded="onAvailableYearsLoaded()"
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
  <TeacherPaymentDialog ref="teacherPaymentDialog" @updated="onUpdated" />
</template>
