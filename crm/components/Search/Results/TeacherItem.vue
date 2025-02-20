<script setup lang="ts">
import type { SearchResultResource } from '..'

const { item } = defineProps<{
  item: SearchResultResource
}>()

const teacher = item.teacher!
</script>

<template>
  <div>
    <div style="width: 200px">
      <UiPerson :item="item" />
    </div>
    <div style="width: 140px" class="text-lowercase">
      {{ EntityTypeLabel[item.entity_type] }}
    </div>
    <div style="width: 200px" :class="{ 'text-gray': teacher.status !== 'active' }">
      {{ TeacherStatusLabel[teacher.status] }}
    </div>
    <div style="width: 250px">
      {{ teacher.subjects.map(s => SubjectLabel[s]).join(', ') }}
    </div>
    <div style="flex: 1">
      <PhoneList :items="item.phones" show-comment />
    </div>
    <!-- <div style="width: 130px; flex: initial" class="text-lowercase text-right text-gray opacity-5">
      {{ EntityTypeLabel[item.entity_type] }}
    </div> -->
  </div>
</template>
