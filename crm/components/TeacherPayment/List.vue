<script setup lang="ts">
import type { TeacherPaymentDialog } from '#build/components'

const { items, create } = defineProps<{
  items: []
  create?: boolean
}>()
const teacherPaymentDialog = ref<InstanceType<typeof TeacherPaymentDialog>>()
</script>

<template>
  <div
    v-for="payment in items"
    :key="payment.id"
  >
    <div
      v-if="payment.teacher"
      style="width: 330px"
    >
      <NuxtLink :to="{ name: 'teachers-id', params: { id: payment.teacher_id } }">
        {{ formatFullName(payment.teacher) }}
      </NuxtLink>
    </div>
    <div style="width: 130px">
      {{ formatDate(payment.date) }}
    </div>
    <div style="width: 180px">
      {{ YearLabel[payment.year] }}
    </div>
    <div style="width: 130px">
      {{ TeacherPaymentMethodLabel[payment.method] }}
    </div>
    <div style="width: 180px">
      {{ formatPrice(payment.sum) }} руб.
    </div>
    <div class="text-right text-gray">
      {{ formatDateTime(payment.created_at) }}
    </div>
  </div>
  <div
    v-if="create"
    style="border: none"
  >
    <a
      class="cursor-pointer"
      @click="teacherPaymentDialog?.create()"
    >добавить платеж</a>
  </div>
  <TeacherPaymentDialog ref="teacherPaymentDialog" />
</template>
