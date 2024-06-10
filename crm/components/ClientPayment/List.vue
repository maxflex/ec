<script setup lang="ts">
const { items } = defineProps<{ items: ClientPaymentResource[] }>()
const emit = defineEmits<{ (e: 'open', p: ClientPaymentResource): void }>()
</script>

<template>
  <div class="table table--hover client-payments">
    <div
      v-for="item in items"
      :key="item.id"
    >
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
      <div>{{ item.sum }} руб.</div>
      <div>
        {{ ClientPaymentMethodLabel[item.method] }}
      </div>
      <div>
        <span
          v-if="item.is_confirmed"
          class="text-success"
        > подтверждён </span>
        <span
          v-else
          class="text-gray"
        > не подтверждён </span>
      </div>
      <div class="table-actions">
        <v-btn
          variant="text"
          icon="$more"
          :size="48"
          @click="emit('open', item)"
        />
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
