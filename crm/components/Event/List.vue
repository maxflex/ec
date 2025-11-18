<script setup lang="ts">
import type { EventListResource } from '.'

const { items } = defineProps<{ items: EventListResource[] }>()

const emit = defineEmits<{
  edit: [id: number]
}>()

const router = useRouter()

function getTotalParticipants(item: EventListResource): number {
  return Object.values(item.participant_counts).reduce((carry, x) => carry + x, 0)
}
</script>

<template>
  <div class="table event-list table--padding table--hover">
    <div v-for="item in items" :key="item.id" class="cursor-pointer" @click="router.push({ name: 'events-id', params: { id: item.id } })">
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          @click.stop="emit('edit', item.id)"
        />
      </div>
      <div class="event-list__img">
        <div v-if="item.file" :style="{ backgroundImage: `url(${item.file.url})` }" />
      </div>
      <div style="width: 100px" class="table-two-lines">
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

      <div style="flex: 1" class="text-truncate pr-2">
        {{ item.name }}
      </div>
      <div style="width: 180px">
        {{ formatName(item.user) }}
      </div>
      <div style="width: 150px" class="table-two-lines">
        <span v-if="getTotalParticipants(item) === 0" class="text-gray">
          нет участников
        </span>
        <span v-else>
          {{ getTotalParticipants(item) }} участников
        </span>
        <div
          v-if="item.me"
          class="event-list__confirmation"
          :class="{
            'text-success': item.me.confirmation === 'confirmed',
            'text-error': item.me.confirmation === 'rejected',
            'text-gray': item.me.confirmation === 'pending',
          }"
        >
          {{ EventParticipantConfirmationLabel[item.me.confirmation] }}
        </div>
        <div v-else class="event-list__counts">
          <span
            v-for="(_, key) in EventParticipantConfirmationLabel"
            :key="key"
            :class="`event-list__counts--${key}`"
          >
            {{ item.participant_counts[key] || 0 }}
          </span>
        </div>
      </div>
      <div style="width: 110px">
        <span v-if="item.telegram_lists_count === 0" class="text-gray">
          нет рассылок
        </span>
        <span v-else>
          {{ item.telegram_lists_count }} рассылок
        </span>
      </div>
    </div>
  </div>
</template>
