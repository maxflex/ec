<script setup lang="ts">
const { items } = defineProps<{ items: [] }>()
</script>

<template>
  <div
    v-for="payment in items"
    :key="payment.id"
  >
    <div
      v-if="payment.teacher"
      style="width: 350px"
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
    <div style="width: 120px">
      {{ TeacherPaymentMethodLabel[payment.method] }}
    </div>
    <div style="width: 200px">
      {{ formatPrice(payment.sum) }} руб.
    </div>
    <div class="text-right text-gray">
      {{ formatDateTime(payment.created_at) }}
    </div>
  </div>
</template>
