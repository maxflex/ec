<script setup lang="ts">
import { mdiCheckAll } from '@mdi/js'

const { items } = defineProps<{
  items: EventListResource[]
}>()

function getParticipantsCount(item: EventListResource): number {
  return Object.values(item.participants).reduce((carry, x) => carry + x, 0)
}
</script>

<template>
  <div class="event-list">
    <div v-for="item in items" :key="item.id">
      <div class="event-list__img">
        <div v-if="item.file" :style="{ backgroundImage: `url(${item.file.url})` }" />
      </div>
      <div class="event-list__date">
        {{ formatDate(item.date) }},
        {{ formatTime(item.time!) }}
        <template v-if="item.time_end">
          – {{ item.time_end }}
        </template>
        <div>
          <span v-if="getParticipantsCount(item) === 0" class="text-gray">
            нет участников
          </span>
          <span v-else>
            {{ getParticipantsCount(item) }} участников
          </span>
        </div>
      </div>

      <div class="event-list__name">
        {{ item.name }}
      </div>

      <div class="event-list__description">
        {{ item.description }}
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

  &__name {
    font-size: 20px;
    font-weight: bold;
  }

  &__description {
  }

  &__confirmation {
    .v-icon {
      font-size: 14px !important;
    }
  }

  &__status,
  &__date {
    display: flex;
    flex-direction: column;
  }

  &__img {
    & > div {
      width: 100%;
      // border-radius: 8px;
      height: 100px;
      background-size: cover;
      background-position: center center;
    }
  }
}
</style>
