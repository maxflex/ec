<script setup lang="ts">
import type { EventListResource } from '.'

const { items } = defineProps<{
  items: EventListResource[]
}>()

function getParticipantsCount(item: EventListResource): number {
  return Object.values(item.participants).reduce((carry, x) => carry + x, 0)
}
</script>

<template>
  <div class="table table--padding event-list">
    <div v-for="item in items" :key="item.id">
      <div class="event-list__img">
        <div v-if="item.file" :style="{ backgroundImage: `url(${item.file.url})` }" />
      </div>

      <div style="width: 100px">
        <span>
          {{ formatDate(item.date) }}
        </span>
        <div class="event-list__confirmation text-gray">
          {{ formatTime(item.time!) }}
          <template v-if="item.time_end">
            – {{ item.time_end }}
          </template>
        </div>
      </div>

      <div style="flex: 1">
        {{ item.name }}
        <div class="event-list__description text-gray">
          {{ item.description }}
        </div>
      </div>
      <div style="width: 150px; flex: initial">
        <span v-if="getParticipantsCount(item) === 0" class="text-gray">
          нет участников
        </span>
        <span v-else>
          {{ getParticipantsCount(item) }} участников
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
    </div>
  </div>
</template>
