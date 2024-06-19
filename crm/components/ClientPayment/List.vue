<script setup lang="ts">
const { items } = defineProps<{ items: ClientPaymentResource[] }>()
const emit = defineEmits<{ (e: 'open', p: ClientPaymentResource): void }>()
</script>

<template>
  <div class="table client-payments">
    <div
      v-for="item in items"
      :id="`client-payment-${item.id}`"
      :key="item.id"
    >
      <div class="table-actionss">
        <v-btn
          variant="plain"
          icon="$edit"
          :size="48"
          @click="emit('open', item)"
        />
      </div>
      <div>
        <span
          v-if="item.is_return"
          class="text-error"
        >
          возврат
        </span>
        <span v-else>
          платеж
        </span>
      </div>
      <div>от {{ formatDate(item.date) }}</div>
      <div>
        {{ YearLabel[item.year] }}
      </div>
      <div>{{ item.sum }} руб.</div>
      <div>
        {{ ClientPaymentMethodLabel[item.method] }}
      </div>
      <div>
        <span
          v-if="item.is_confirmed"
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
  </div>
</template>

<style lang="scss">
.client-payments {
  & > div {
    & > div {
      &:nth-child(2) {
        width: 150px;
      }
      &:nth-child(3),
      &:nth-child(4) {
        width: 220px;
      }
      &:nth-child(5),
      &:nth-child(6) {
        width: 150px;
      }
    }
  }
}
</style>
