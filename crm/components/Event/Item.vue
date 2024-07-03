<script setup lang="ts">
import { mdiAccountGroup } from '@mdi/js'

const { item } = defineProps<{ item: EventListResource }>()

defineEmits<{
  edit: [e: EventListResource]
}>()
</script>

<template>
  <div :id="`event-${item.id}`">
    <div class="table-actionss">
      <v-btn
        icon="$edit"
        :size="48"
        variant="plain"
        color="gray"
        @click="$emit('edit', item)"
      />
    </div>
    <div style="width: 110px" />
    <div style="width: 120px">
      {{ formatTime(item.time!) }}
      <template v-if="item.time_end">
        – {{ item.time_end }}
      </template>
    </div>
    <div style="width: 440px">
      {{ item.name }}
    </div>
    <div style="width: 80px; display: flex; align-items: center">
      <v-icon :icon="mdiAccountGroup" class="mr-2 vfn-1" />
      {{ item.participants_count }}
    </div>
    <div style="width: 140px">
      <div class="event-status">
        <div class="event-status__circle" />
        событие
      </div>
    </div>
    <div>
      <v-chip v-if="item.is_afterclass" class="text-purple">
        внеклассное
      </v-chip>
    </div>
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
