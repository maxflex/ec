<script setup lang="ts">
import type { AlfaPaymentResource } from '.'

const { items } = defineProps<{
  items: AlfaPaymentResource[]
}>()

const emit = defineEmits<{
  create: [e: AlfaPaymentResource]
}>()
</script>

<template>
  <Table hoverable>
    <TableRow v-for="item in items" :key="item.id">
      <TableCol :width="230">
        <UiPerson :item="item.client" />
      </TableCol>
      <TableCol :width="100">
        {{ formatDate(item.date) }}
      </TableCol>
      <TableCol :width="120" :class="{ 'text-error': item.is_return }">
        {{ formatPrice(item.sum) }} руб.
      </TableCol>
      <TableCol :width="70">
        {{ ContractPaymentMethodLabel[item.method] }}
      </TableCol>
      <TableCol :width="70">
        {{ CompanyLabel[item.contract.company] }}
      </TableCol>
      <TableCol :width="150">
        договор №{{ item.contract_id }}
      </TableCol>
      <TableCol class="text-gray" :width="140">
        не подтверждён
      </TableCol>
      <TableButtons>
        <v-btn density="comfortable" color="primary" @click="emit('create', item)">
          создать платеж
        </v-btn>
      </TableButtons>
    </TableRow>
  </Table>
</template>
