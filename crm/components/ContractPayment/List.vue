<script setup lang="ts">
import type { ContractPaymentResource } from '.'

const { items } = defineProps<{ items: ContractPaymentResource[] }>()
const emit = defineEmits<{
  edit: [id: number]
}>()
</script>

<template>
  <Table class="contract-payments">
    <TableRow
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
      <TableCol :width="150">
        <span
          v-if="item.is_return"
          class="text-error"
        >
          возврат
        </span>
        <span v-else>
          платеж
        </span>
      </TableCol>
      <TableCol :width="200">
        {{ formatDate(item.date) }}
      </TableCol>

      <TableCol :width="200">
        {{ ContractPaymentMethodLabel[item.method] }}
        <div v-if="item.pko_number" class="text-gray text-caption">
          ПКО: {{ item.pko_number }}
        </div>
      </TableCol>
      <TableCol :width="200">
        <UiPaymentConfirm :item="item" />
      </TableCol>
      <TableCol>
        {{ formatPrice(item.sum) }} руб.
      </TableCol>
    </TableRow>
  </Table>
</template>
