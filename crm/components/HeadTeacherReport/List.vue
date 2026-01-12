<script setup lang="ts">
import type { HeadTeacherReportResource } from '.'

const { items } = defineProps<{
  items: HeadTeacherReportResource[]
  showTeacher?: boolean
}>()
defineEmits<{
  edit: [item: HeadTeacherReportResource]
}>()
</script>

<template>
  <Table>
    <TableRow
      v-for="item in items"
      :id="`head-teacher-report-${item.id}`"
      :key="item.id"
    >
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          @click="$emit('edit', item)"
        />
      </div>
      <TableCol v-if="showTeacher" :width="180">
        <UiPerson :item="item.teacher!" />
      </TableCol>
      <TableCol :width="120">
        {{ MonthLabel[item.month] }}
      </TableCol>
      <TableCol class="text-truncate">
        {{ filterTruncate(item.text, 60) }}
      </TableCol>
      <TableCol class="text-gray" style="width: 80px; flex: initial">
        {{ formatDate(item.created_at!) }}
      </TableCol>
    </TableRow>
  </Table>
</template>

<style lang="scss">
.head-teacher-reports {
  table {
    table-layout: fixed;
    tr {
      position: relative;
    }
  }
}
</style>
