<script setup lang="ts">
import type { EventListResource } from '.'

const { items } = defineProps<{
  items: EventListResource[]
}>()
</script>

<template>
  <div class="table table--padding event-list">
    <div v-for="item in items" :key="item.id">
      <div style="width: 100px">
        {{ formatDate(item.date) }}
      </div>
      <div style="width: 110px">
        {{ formatTime(item.time!) }}
        <template v-if="item.time_end">
          – {{ item.time_end }}
        </template>
      </div>
      <div style="flex: 1">
        {{ item.name }}
        <div class="event-list__description text-gray">
          {{ item.description }}
        </div>
      </div>
      <div style="width: 150px">
        <span v-if="item.participants_count === 0" class="text-gray">
          нет участников
        </span>
        <span v-else>
          {{ item.participants_count }} участников
        </span>
        <div
          v-if="item.participant"
          class="event-list__confirmation"
          :class="{
            'text-success': item.participant.confirmation === 'confirmed',
            'text-error': item.participant.confirmation === 'rejected',
            'text-gray': item.participant.confirmation === 'pending',
          }"
        >
          {{ EventParticipantConfirmationLabel[item.participant.confirmation] }}
        </div>
      </div>
      <div class="event-list__status">
        {{ item.is_afterclass ? 'внеклассное' : 'учебное' }}
      </div>
    </div>
  </div>
</template>
