<script setup lang="ts">
const { item } = defineProps<{ item: EventListResource }>()
</script>

<template>
  <div
    :id="`event-${item.id}`"
    class="lesson-item event-item"
  >
    <div style="width: 120px">
      {{ formatTime(item.time!) }}
      <template v-if="item.time_end">
        – {{ item.time_end }}
      </template>
    </div>
    <div style="width: 60px">
    </div>
    <div style="width: 150px">
      {{ formatName(item.user) }}
    </div>
    <div style="width: 250px">
      {{ item.name }}
    </div>
    <div style="width: 156px">
      {{ item.participants_count }} участников
    </div>
    <div>
      {{ item.is_afterclass ? 'внеклассное' : 'учебное' }} событие
    </div>
  </div>
</template>
