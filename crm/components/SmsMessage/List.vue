<script setup lang="ts">
import type { SmsMessageListResource } from '.'

const { items } = defineProps<{
  items: SmsMessageListResource[]
}>()
</script>

<template>
  <Table>
    <TableRow v-for="item in items" :key="item.id">
      <TableCol :width="160">
        {{ formatPhone(item.number) }}
      </TableCol>
      <TableCol class="text-truncate pr-4" :title="item.text">
        {{ item.text }}
      </TableCol>
      <TableCol :width="200" class="text-lowercase" :class="{ 'text-success': item.status === 1 }">
        {{ item.status_name }}
      </TableCol>
      <TableCol class="text-gray" style="width: 130px; flex: initial">
        {{ formatDateTime(item.created_at) }}
      </TableCol>
    </TableRow>
  </Table>
</template>
