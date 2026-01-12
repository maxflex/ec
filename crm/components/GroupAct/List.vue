<script setup lang="ts">
const { items } = defineProps<{
  items: GroupActResource[]
}>()
const emit = defineEmits<{
  edit: [item: GroupActResource]
}>()
</script>

<template>
  <Table>
    <TableRow v-for="item in items" :id="`group-act-${item.id}`" :key="item.id">
      <TableCol :width="150">
        Акт №{{ item.id }}
      </TableCol>
      <TableCol :width="180">
        <UiPerson :item="item.teacher!" />
      </TableCol>
      <TableCol :width="150">
        {{ plural(item.lessons!, ['занятие', 'занятия', 'занятий']) }}
      </TableCol>
      <TableCol :width="150">
        {{ formatPrice(item.sum!) }} руб.
      </TableCol>
      <TableCol style="flex: initial">
        {{ formatDate(item.date) }}
      </TableCol>
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          @click="emit('edit', item)"
        />
      </div>
    </TableRow>
  </Table>
</template>
