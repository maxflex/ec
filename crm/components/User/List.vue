<script setup lang="ts">
const { items } = defineProps<{ items: UserResource[] }>()
const emit = defineEmits<{
  edit: [userId: number]
}>()
</script>

<template>
  <Table class="table--actions-on-hover">
    <TableRow
      v-for="item in items"
      :key="item.id"
    >
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          color="gray"
          @click="emit('edit', item.id)"
        />
      </div>
      <TableCol :width="30">
        {{ item.id }}
      </TableCol>
      <TableCol :width="300">
        {{ formatName(item) }}
      </TableCol>

      <TableCol>
        <PhoneList
          :items="item.phones"
          style="width: 250px"
        />
      </TableCol>
      <TableCol :width="300">
        <span :class="{ 'text-gray': !item.is_active }">
          {{ UserStatusLabel[Number(item.is_active)] }}
        </span>
        <!-- <span v-if="item.is_active">
          действующий сотрудник
        </span>
        <span v-else class="text-gray">
          больше не работает
        </span> -->
      </TableCol>
      <TableCol
        class="text-gray"
        style="width: 150px; flex: initial"
      >
        {{ formatDateTime(item.created_at!) }}
      </TableCol>
    </TableRow>
  </Table>
</template>
