<script setup lang="ts">
import type { TeacherPaymentDialog } from '#build/components'
import type { TeacherPaymentResource } from '.'

const { items } = defineProps<{
  items: TeacherPaymentResource[]
}>()
const { isAdmin } = useAuthStore()
const teacherPaymentDialog = ref<InstanceType<typeof TeacherPaymentDialog>>()

function onUpdated(tp: TeacherPaymentResource) {
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

function onDeleted(tp: TeacherPaymentResource) {
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
    <div v-for="payment in items" :key="payment.id">
      <UiTableActions v-if="isAdmin">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          color="gray"
          @click="() => teacherPaymentDialog?.edit(payment)"
        />
</UiTableActions>
      <div v-if="isAdmin" style="width: 200px">
        <UiPerson :item="payment.teacher!" />
      </div>
      <div style="width: 180px">
        {{ formatPrice(payment.sum) }} руб.
      </div>
      <div style="width: 180px">
        {{ TeacherPaymentMethodLabel[payment.method] }}
      </div>
      <div style="width: 150px">
        {{ formatDate(payment.date) }}
      </div>
      <div>
        <span
          v-if="payment.is_confirmed"
          class="text-success"
        >
          подтверждён
        </span>
        <span
          v-else
          class="text-gray"
        >
          не подтверждён
        </span>
      </div>
    </div>
    <TeacherPaymentDialog
      v-if="isAdmin"
      ref="teacherPaymentDialog"
      @updated="onUpdated"
      @destroyed="onDeleted"
    />
  </div>
</template>
