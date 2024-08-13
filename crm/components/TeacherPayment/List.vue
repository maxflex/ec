<script setup lang="ts">
import type { TeacherPaymentDialog } from '#build/components'

const { items, teacherId } = defineProps<{
  items: TeacherPaymentResource[]
  teacherId?: number
}>()
const teacherPaymentDialog = ref<InstanceType<typeof TeacherPaymentDialog>>()

function onTeacherPaymentUpdated(tp: TeacherPaymentResource) {
  const index = items.findIndex(e => e.id === tp.id)
  if (index !== -1) {
    // eslint-disable-next-line
    items.splice(index, 1, tp)
  }
  else {
    // eslint-disable-next-line
    items.push(tp)
    smoothScroll('main', 'bottom')
  }
}

function onTeacherPaymentDestroyed(tp: TeacherPaymentResource) {
  const index = items.findIndex(e => e.id === tp.id)
  if (index !== -1) {
    // eslint-disable-next-line
    items.splice(index, 1)
  }
}
</script>

<template>
  <div
    class="table"
  >
    <div
      v-for="payment in items"
      :key="payment.id"
    >
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          color="gray"
          @click="() => teacherPaymentDialog?.edit(payment)"
        />
      </div>
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
        {{ TeacherPaymentMethodLabel[payment.method] }}
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
        @click="teacherPaymentDialog?.create(teacherId!)"
      >добавить платеж</a>
    </div>
    <TeacherPaymentDialog
      ref="teacherPaymentDialog"
      @updated="onTeacherPaymentUpdated"
      @destroyed="onTeacherPaymentDestroyed"
    />
  </div>
</template>
