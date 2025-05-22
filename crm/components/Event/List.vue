<script setup lang="ts">
import { mdiCheckAll } from '@mdi/js'

const { items } = defineProps<{
  items: EventListResource[]
}>()

const emit = defineEmits<{
  edit: [id: number]
}>()
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
