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
      <div style="width: 400px">
        {{ item.programs.map(e => ProgramShortLabel[e]).join(', ') }}
      </div>
      <div style="width: 150px">
        <span v-if="item.dates.filter(e => e.is_reserve === 0).length === 0" class="text-gray">
          не установлено
        </span>
        <span v-else class="text-deepOrange">
          {{ plural(item.dates.filter(e => e.is_reserve === 0).length, ['дата', 'даты', 'дат']) }}
        </span>
      </div>
      <div>
        <span v-if="item.dates.filter(e => e.is_reserve === 1).length === 0" class="text-gray">
          не установлено
        </span>
        <span v-else class="text-purple">
          {{ plural(item.dates.filter(e => e.is_reserve === 1).length, ['дата', 'даты', 'дат']) }}
        </span>
      </div>
    </div>
  </div>
</template>
