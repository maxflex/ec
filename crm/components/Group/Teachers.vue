<script setup lang="ts">
const { item } = defineProps<{ item: GroupListResource }>()
</script>

<template>
  <UiIfSet :value="!!item.teachers.length">
    <template #empty>
      преподавателей нет
    </template>
    <div v-for="t in item.teachers" :key="t.id">
      <UiPerson :item="t" no-link />
      <span class="counts-by-teacher">
        {{ item.teacher_counts[t.id] }}
      </span>
    </div>
  </UiIfSet>
</template>

<style lang="scss">
.counts-by-teacher {
  color: rgb(var(--v-theme-gray));
  font-size: 14px;
  margin-left: 8px;
}
</style>
