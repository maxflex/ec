<script setup lang="ts">
const { items } = defineProps<{
  items: HeadTeacherReportResource[]
  showTeacher?: boolean
}>()
defineEmits<{
  edit: [item: HeadTeacherReportResource]
}>()
</script>

<template>
  <v-table hover class="head-teacher-reports">
    <tbody>
      <tr
        v-for="item in items"
        :id="`head-teacher-report-${item.id}`"
        :key="item.id"
        class="cursor-pointer"
        @click="$emit('edit', item)"
      >
        <td v-if="showTeacher" width="180">
          <UiPerson :item="item.teacher!" />
        </td>
        <td width="120">
          {{ MonthLabel[item.month] }}
        </td>
        <td>
          <div class="text-truncate">
            {{ item.text }}
          </div>
        </td>
        <td class="text-gray" width="180">
          {{ formatDateTime(item.created_at!) }}
        </td>
      </tr>
    </tbody>
  </v-table>
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
