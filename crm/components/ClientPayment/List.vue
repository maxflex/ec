<script setup lang="ts">
import type { ClientPayments } from "~/utils/models"

const { items } = defineProps<{
  items: ClientPayments
}>()
</script>

<template>
  <div class="table client-payments">
    <div v-for="item in items" :key="item.id">
      <div>
        {{ item.type === "payment" ? "платеж" : "возврат" }}
      </div>
      <div>от {{ formatDate(item.date) }}</div>
      <div>{{ item.sum }} руб.</div>
      <div>
        {{ item.method }}
      </div>
      <div>
        <span v-if="item.is_confirmed" class="text-success"> подтверждён </span>
        <span v-else class="text-gray"> не подтверждён </span>
      </div>
      <div class="text-right">
        <v-btn icon="$more" :size="48" />
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.client-payments {
  & > div {
    gap: 0px !important;
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
