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
  <Table>
    <TableRow v-for="payment in items" :key="payment.id">
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          color="gray"
          @click="() => teacherServiceDialog?.edit(payment)"
        />
      </div>
      <TableCol
        v-if="!teacherId"
        :width="330"
      >
        <NuxtLink :to="{ name: 'teachers-id', params: { id: payment.teacher_id } }">
          {{ formatFullName(payment.teacher!) }}
        </NuxtLink>
      </TableCol>
      <TableCol :width="150">
        {{ formatDate(payment.date) }}
      </TableCol>
      <TableCol :width="180">
        {{ formatPrice(payment.sum) }} руб.
      </TableCol>
      <TableCol
        class="text-truncate"
      >
        {{ payment.purpose }}
      </TableCol>
      <TableCol
        v-if="teacherId"
        class="text-gray"
        style="width: 150px; flex: initial"
      >
        {{ formatDateTime(payment.created_at!) }}
      </TableCol>
    </TableRow>
    <TableRow
      v-if="teacherId"
      style="border: none"
    >
      <a
        class="cursor-pointer"
        @click="teacherServiceDialog?.create(teacherId!)"
      >добавить допуслугу</a>
    </TableRow>
    <TeacherServiceDialog
      ref="teacherServiceDialog"
      @updated="onTeacherServiceUpdated"
      @deleted="onTeacherServiceDeleted"
    />
  </Table>
</template>
