<script setup lang="ts">
import type { TeacherServiceDialog } from '#build/components'
import type { TeacherServiceResource } from '.'

const { items, teacherId } = defineProps<{
  items: TeacherServiceResource[]
  teacherId?: number
}>()
const teacherServiceDialog = ref<InstanceType<typeof TeacherServiceDialog>>()

function onTeacherServiceUpdated(ts: TeacherServiceResource) {
  const index = items.findIndex(e => e.id === ts.id)
  if (index !== -1) {
    // eslint-disable-next-line
    items.splice(index, 1, ts)
  }
  else {
    // eslint-disable-next-line
    items.push(ts)
    smoothScroll('main', 'bottom')
  }
}

function onTeacherServiceDeleted(ts: TeacherServiceResource) {
  const index = items.findIndex(e => e.id === ts.id)
  if (index !== -1) {
    // eslint-disable-next-line
    items.splice(index, 1)
  }
}
</script>

<template>
  <div class="table">
    <div v-for="payment in items" :key="payment.id">
      <UiTableActions>
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          color="gray"
          @click="() => teacherServiceDialog?.edit(payment)"
        />
</UiTableActions>
      <div
        v-if="!teacherId"
        style="width: 330px"
      >
        <NuxtLink :to="{ name: 'teachers-id', params: { id: payment.teacher_id } }">
          {{ formatFullName(payment.teacher!) }}
        </NuxtLink>
      </div>
      <div style="width: 150px">
        {{ formatDate(payment.date) }}
      </div>
      <div style="width: 180px">
        {{ formatPrice(payment.sum) }} руб.
      </div>
      <div
        style="flex: 1"
        class="text-truncate"
      >
        {{ payment.purpose }}
      </div>
      <div
        v-if="teacherId"
        class="text-gray"
        style="width: 150px; flex: initial"
      >
        {{ formatDateTime(payment.created_at!) }}
      </div>
    </div>
    <div
      v-if="teacherId"
      style="border: none"
    >
      <a
        class="cursor-pointer"
        @click="teacherServiceDialog?.create(teacherId!)"
      >добавить допуслугу</a>
    </div>
    <TeacherServiceDialog
      ref="teacherServiceDialog"
      @updated="onTeacherServiceUpdated"
      @deleted="onTeacherServiceDeleted"
    />
  </div>
</template>
