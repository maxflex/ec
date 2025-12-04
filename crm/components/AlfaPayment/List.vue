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
  <Table>
    <TableRow v-for="item in items" :key="item.id">
      <TableCol :width="250">
        <UiPerson :item="item.client" />
      </TableCol>
      <TableCol :width="100">
        {{ formatDate(item.date) }}
      </TableCol>
      <TableCol :width="140" :class="{ 'text-error': item.is_return }">
        {{ formatPrice(item.sum) }} руб.
      </TableCol>
      <TableCol :width="80">
        {{ ContractPaymentMethodLabel[item.method] }}
      </TableCol>
      <TableCol :width="70">
        {{ CompanyLabel[item.contract.company] }}
      </TableCol>
      <TableCol :width="160">
        договор №{{ item.contract_id }}
      </TableCol>
      <TableCol class="text-gray" :width="140">
        не подтверждён
      </TableCol>
      <TableButtons>
        <v-btn icon="$edit" :size="48" variant="plain" color="gray" @click="emit('create', item)" />
      </TableButtons>
    </TableRow>
  </Table>
</template>
