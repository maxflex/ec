<script setup lang="ts">
import type { ErrorResource } from '.'

const { items } = defineProps<{
  items: ErrorResource[]
}>()
</script>

<template>
  <Table>
    <TableRow v-for="item in items" :key="item.id">
      <TableCol :width="300">
        <UiPerson :item="item.person" />
      </TableCol>
      <TableCol>
        <template v-if="item.code === 1000">
          {{ item.number || 'телефон не указан' }}
        </template>
        <template v-else-if="item.code === 2000">
          договор №{{ item.entity_id }}
        </template>
      </TableCol>
      <TableCol style="width: 100px; flex: initial">
        <code>
          {{ item.code }}
        </code>
      </TableCol>
    </TableRow>
  </Table>
</template>
