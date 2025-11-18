<script lang="ts" setup>
import type { EventListResource } from '.'

const { item } = defineProps<{
  item: EventListResource
}>()
</script>

<template>
  <div class="event-item">
    <div class="font-weight-bold">
      {{ item.name }}
    </div>
    <div class="event-item__row">
      <div class="event-item__img">
        <div v-if="item.file" :style="{ backgroundImage: `url(${item.file.url})` }" />
      </div>
      <div class="event-item__info">
        <div>
          {{ formatDate(item.date) }}
        </div>
        <div>
          {{ formatTime(item.time!) }}
          <template v-if="item.time_end">
            – {{ item.time_end }}
          </template>
        </div>
        <div v-if="item.participant_counts">
          <span v-if="item.participant_counts.confirmed === 0" class="text-gray">
            нет участников
          </span>
          <span v-else>
            {{ item.participant_counts.confirmed }} участников
          </span>
        </div>
        <div
          v-if="item.me"
          :class="{
            'text-success': item.me.confirmation === 'confirmed',
            'text-error': item.me.confirmation === 'rejected',
            'text-gray': item.me.confirmation === 'pending',
          }"
        >
          {{ EventParticipantConfirmationLabel[item.me.confirmation] }}
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.event-item {
  font-size: 14px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;

  &__img {
    & > div {
      width: 100%;
      border-radius: 8px;
      height: 84px;
      background-size: cover;
      background-position: center center;
    }
  }

  &__row {
    display: flex;
    gap: 16px;

    & > div {
      flex: 1;
      // &:first-child {
      //   width: 100px;
      // }
      // &:last-child {
      //   flex: 1;
      // }
    }
  }
}
</style>
