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
  <div class="table">
    <div
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
      <div v-if="showTeacher" style="width: 180px">
        <UiPerson :item="item.teacher!" />
      </div>
      <div style="width: 120px">
        {{ MonthLabel[item.month] }}
      </div>
      <div style="flex: 1" class="text-truncate">
        {{ filterTruncate(item.text, 60) }}
      </div>
      <div class="text-gray" style="width: 80px; flex: initial">
        {{ formatDate(item.created_at!) }}
      </div>
    </div>
  </div>
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
