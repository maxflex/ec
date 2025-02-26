<script setup lang="ts">
import type { SearchResultResource } from '..'

const { item } = defineProps<{
  item: SearchResultResource
}>()

const teacher = item.teacher!
</script>

<template>
  <RouterLink :to="{ name: 'teachers-id', params: { id: item.id } }" class="search-teacher-item">
    <div style="width: 200px">
      <UiPerson :item="item" no-link />
    </div>
    <div style="width: 140px" class="text-lowercase">
      {{ EntityTypeLabel[item.entity_type] }}
    </div>
    <div style="width: 200px">
      {{ teacher.subjects.map(s => SubjectLabel[s]).join(', ') }}
    </div>
    <div style="width: 250px" :class="{ 'text-success': item.is_active }">
      {{ TeacherStatusLabel[teacher.status] }}
    </div>
    <div style="flex: 1">
      <PhoneList :items="item.phones" show-comment />
    </div>
    <!-- <div style="width: 130px; flex: initial" class="text-lowercase text-right text-gray opacity-5">
      {{ EntityTypeLabel[item.entity_type] }}
    </div> -->
  </RouterLink>
</template>

<style lang="scss">
.search-teacher-item {
  & > div {
    padding: 16px 0;
  }
}
</style>
