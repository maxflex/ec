<script setup lang="ts">
const { items } = defineProps<{
  items: InstructionListResource[]
}>()
</script>

<template>
  <Table hoverable>
    <NuxtLink
      v-for="item in items"
      :id="`instruction-${item.id}`"
      :key="item.id"
      class="table-item"
      :to="{ path: `${$route.path}/${item.id}` }"
    >
      <TableCol>
        {{ item.title }}
      </TableCol>
      <TableCol :width="220">
        {{ InstructionStatusLabel[item.status] }}
      </TableCol>
      <TableCol :width="120">
        {{ plural(item.versions_count, ['версия', 'версии', 'версий']) }}
      </TableCol>
      <TableCol :width="160">
        {{ item.signs_needed - item.signs_count }} не подписали
      </TableCol>
      <TableCol style="width: 150px; flex: initial" class="text-gray">
        {{ formatDateTime(item.created_at) }}
      </TableCol>
    </NuxtLink>
  </Table>
</template>
