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
  <Table>
    <TableRow v-for="payment in items" :key="payment.id">
      <div v-if="isAdmin" class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          color="gray"
          @click="() => teacherPaymentDialog?.edit(payment)"
        />
      </div>
      <TableCol v-if="isAdmin" :width="200">
        <UiPerson :item="payment.teacher!" />
      </TableCol>
      <TableCol :width="180">
        {{ formatPrice(payment.sum) }} руб.
      </TableCol>
      <TableCol :width="180">
        {{ TeacherPaymentMethodLabel[payment.method] }}
      </TableCol>
      <TableCol :width="150">
        {{ formatDate(payment.date) }}
      </TableCol>
      <TableCol>
        <UiPaymentConfirm :item="payment" />
      </TableCol>
    </TableRow>
    <TeacherPaymentDialog
      v-if="isAdmin"
      ref="teacherPaymentDialog"
      @updated="onUpdated"
      @destroyed="onDeleted"
    />
  </Table>
</template>
