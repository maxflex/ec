<script setup lang="ts">
const { items } = defineProps<{
  items: InstructionTeacherListResource[]
}>()
</script>

<template>
  <Table hoverable>
    <NuxtLink
      v-for="item in items"
      :id="`instruction-${item.id}`"
      :key="item.id"
      class="table-item"
      :to="{ name: 'instructions-id', params: { id: item.id } }"
    >
      <TableCol>
        {{ item.title }}
      </TableCol>
      <TableCol :width="250">
        <v-chip v-if="item.signed_at" color="success">
          подписано {{ formatDateTime(item.signed_at) }}
        </v-chip>
        <v-chip v-else color="error">
          не подписано
        </v-chip>
      </TableCol>
      <TableCol style="width: 150px; flex: initial" class="text-gray">
        {{ formatDateTime(item.created_at) }}
      </TableCol>
    </NuxtLink>
  </Table>
</template>
