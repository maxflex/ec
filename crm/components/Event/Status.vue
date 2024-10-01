<script setup lang="ts">
const { item } = defineProps<{
  item: EventListResource
}>()
</script>

<template>
  <div class="event-status">
    <div class="event-status__circle" />
    {{ item.is_afterclass ? 'внеклассное' : 'учебное' }}
    событие
  </div>
</template>

<style lang="scss">
.event-status {
  display: flex;
  align-items: center;
  gap: 4px;
  --color: #9c27b0;
  color: var(--color);
  &__circle {
    --size: 8px;
    height: var(--size);
    width: var(--size);
    border-radius: 50%;
    background-color: var(--color);
    top: 1px;
    position: relative;
  }
}
</style>
