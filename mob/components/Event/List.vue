<script setup lang="ts">
import { mdiCheckAll } from '@mdi/js'

const { items } = defineProps<{
  items: EventListResource[]
}>()
</script>

<template>
  <div class="event-list">
    <div v-for="item in items" :key="item.id">
      <div class="event-list__name">
        <span class="font-weight-medium">
          {{ item.name }}
        </span>
        <div class="event-list__description text-gray">
          {{ item.description }}
        </div>
      </div>
      <div
        v-if="item.participant"
        class="event-list__confirmation"
        :class="{
          'text-success': item.participant.confirmation === 'confirmed',
          'text-error': item.participant.confirmation === 'rejected',
          'text-gray': item.participant.confirmation === 'pending',
        }"
      >
        <v-icon
          :icon="item.participant.confirmation === 'confirmed' ? mdiCheckAll : (item.participant.confirmation === 'rejected' ? '$close' : '$complete')"
          class="vfn-1"
        />
        {{ EventParticipantConfirmationLabel[item.participant.confirmation] }}
      </div>

      <div class="event-list__status">
        <span v-if="item.participants_count === 0" class="text-gray">
          нет участников
        </span>
        <span v-else>
          {{ item.participants_count }} участников
        </span>
        <div>
          {{ item.is_afterclass ? 'внеклассное' : 'учебное' }}
        </div>
      </div>
      <div class="event-list__date">
        <span>
          {{ formatDate(item.date) }}
        </span>
        <span>
          {{ formatTime(item.time!) }}
          <template v-if="item.time_end">
            – {{ item.time_end }}
          </template>
        </span>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.event-list {
  font-size: 14px;

  & > div {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    &:not(:last-child) {
      border-bottom: 1px solid rgb(var(--v-theme-border));
    }
  }

  &__confirmation {
    .v-icon {
      font-size: 14px !important;
    }
  }

  &__status,
  &__date {
    display: flex;
    gap: 10px;
  }
}
</style>
