<script setup lang="ts">
import type { ClientPayments } from "~/utils/models"
import { CLIENT_PAYMENT_METHOD } from "~/utils/sment"

const { items } = defineProps<{
  items: ClientPayments
}>()
</script>

<template>
  <div class="table table--hover client-payments">
    <div v-for="item in items" :key="item.id">
      <div>
        {{ item.is_return ? "возврат" : "платеж" }}
      </div>
      <div>от {{ formatDate(item.date) }}</div>
      <div>{{ item.sum }} руб.</div>
      <div>
        {{ CLIENT_PAYMENT_METHOD[item.method] }}
      </div>
      <div>
        <span v-if="item.is_confirmed" class="text-success"> подтверждён </span>
        <span v-else class="text-gray"> не подтверждён </span>
      </div>
      <div class="table-actions">
        <v-btn variant="text" icon="$more" :size="48" />
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.client-payments {
  & > div {
    & > div {
      &:nth-child(1) {
        width: 150px;
      }
      &:nth-child(2),
      &:nth-child(3),
      &:nth-child(4) {
        width: 220px;
      }
    }
  }
}
</style>
