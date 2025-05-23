<script setup lang="ts">
const { items } = defineProps<{
  items: EventListResource[]
}>()

const emit = defineEmits<{
  edit: [id: number]
}>()

function getParticipantsCount(item: EventListResource): number {
  return Object.values(item.participants).reduce((carry, x) => carry + x, 0)
}
</script>

<template>
  <div class="table event-list">
    <div v-for="item in items" :key="item.id">
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          @click="emit('edit', item.id)"
        />
      </div>
      <div style="width: 100px">
        {{ formatDate(item.date) }}
      </div>
      <div style="width: 110px">
        {{ formatTime(item.time!) }}
        <template v-if="item.time_end">
          – {{ item.time_end }}
        </template>
      </div>
      <div style="flex: 1" class="text-truncate pr-2">
        <RouterLink :to="{ name: 'events-id', params: { id: item.id } }">
          {{ item.name }}
        </RouterLink>
      </div>
      <div style="width: 180px">
        {{ formatName(item.user) }}
      </div>
      <div style="width: 150px" class="event-list__participants">
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
        <div v-else class="event-list__counts">
          <span
            v-for="(_, key) in EventParticipantConfirmationLabel"
            :key="key"
            :class="`event-list__counts--${key}`"
          >
            {{ item.participants[key] || 0 }}
          </span>
        </div>
      </div>
      <div style="width: 110px;">
        <span v-if="item.telegram_lists_count === 0" class="text-gray">
          нет рассылок
        </span>
        <span v-else>
          {{ item.telegram_lists_count }} рассылок
        </span>
      </div>
      <div class="event-list__status">
        <div>
          {{ item.is_afterclass ? 'внеклассное' : 'учебное' }}
        </div>
        <div v-if="item.is_private" class="text-purple">
          конфиденциальное
        </div>
      </div>
    </div>
  </div>
</template>
