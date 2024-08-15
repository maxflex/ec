<script setup lang="ts">
import { getDate, getMonth } from 'date-fns'

const { items } = defineProps<{
  items: ExamDateResource[]
}>()
defineEmits<{
  edit: [item: ExamDateResource]
}>()

function formatExamDate(d: string) {
  const month = MonthLabelDative[getMonth(d)]
  const day = getDate(d)
  return `${day} ${month}`
}
</script>

<template>
  <div class="table">
    <div v-for="item in items" :id="`exam-date-${item.id}`" :key="item.id">
      <div style="width: 300px">
        {{ ExamLabel[item.exam] }}
      </div>
      <div>
        <span v-if="item.dates.length === 0" class="text-gray">
          не установлено
        </span>
        <span v-else>
          {{ item.dates.map(formatExamDate).join(', ') }}
        </span>
      </div>
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          @click="$emit('edit', item)"
        />
      </div>
    </div>
  </div>
</template>
