<script setup lang="ts">
const { items } = defineProps<{
  items: ExamDateResource[]
}>()
defineEmits<{
  edit: [item: ExamDateResource]
}>()
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
          {{ item.dates.map(formatDate).join(', ') }}
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
