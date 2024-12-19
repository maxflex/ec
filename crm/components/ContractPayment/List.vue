<script setup lang="ts">
const { items } = defineProps<{ items: ContractPaymentResource[] }>()
const emit = defineEmits<{
  edit: [id: number]
}>()
</script>

<template>
  <div class="table contract-payments">
    <div
      v-for="item in items"
      :id="`contract-payment-${item.id}`"
      :key="item.id"
    >
      <div class="table-actionss">
        <v-btn
          variant="plain"
          icon="$edit"
          :size="48"
          @click="emit('edit', item.id)"
        />
      </div>
      <div style="width: 150px">
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
      <div style="width: 220px">
        от {{ formatDate(item.date) }}
      </div>
      <div style="width: 220px">
        {{ formatPrice(item.sum) }} руб.
      </div>
      <div style="width: 220px">
        {{ ContractPaymentMethodLabel[item.method] }}
        <div v-if="item.pko_number" class="text-gray text-caption">
          ПКО: {{ item.pko_number }}
        </div>
      </div>
      <div>
        <span v-if="item.is_confirmed" class="text-success">
          подтверждён
        </span>
        <span v-else class="text-gray">
          не подтверждён
        </span>
      </div>
    </div>
  </div>
</template>
