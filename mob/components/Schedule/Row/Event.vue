<script setup lang="ts">
const { item } = defineProps<{ item: EventListResource }>()
</script>

<template>
  <div
    :id="`event-${item.id}`"
    class="lesson-item event-item"
  >
    <div class="lesson-item__row">
      <div class="font-weight-medium">
        {{ formatTime(item.time!) }}
        <template v-if="item.time_end">
          – {{ item.time_end }}
        </template>
      </div>
      <div>
        {{ item.participants_count }} участников
      </div>
    </div>
    <div class="lesson-item__row">
      <div>
        {{ item.name }}
      </div>
      <div>
        {{ item.is_afterclass ? 'внеклассное' : 'учебное' }} событие
      </div>
    </div>
  </div>
</template>
