<script setup lang="ts">
const { items } = defineProps<{
  items: ExamDateResource[]
}>()
defineEmits<{
  edit: [item: ExamDateResource]
}>()
</script>

<template>
  <div class="table table--hover">
    <div
      v-for="item in items"
      :id="`exam-date-${item.id}`"
      :key="item.id"
      class="cursor-pointer"
      @click="$emit('edit', item)"
    >
      <div style="width: 300px">
        {{ ExamLabel[item.exam] }}
      </div>
      <div style="width: 300px">
        {{ item.programs.map(e => ProgramShortLabel[e]).join(', ') }}
      </div>
      <div>
        <span v-if="item.dates.length === 0" class="text-gray">
          не установлено
        </span>
        <span v-else>
          {{ plural(item.dates.length, ['дата', 'даты', 'дат']) }}
        </span>
      </div>
    </div>
  </div>
</template>
