<script setup lang="ts">
import type { EventListResource, EventResource } from '.'
import { mdiCheckAll } from '@mdi/js'

const { item } = defineProps<{
  item: EventResource | EventListResource
}>()

const emit = defineEmits<{
  edit: [id: number]
}>()

const { isAdmin } = useAuthStore()

function getParticipantsCount(item: EventListResource): number {
  return Object.values(item.participants).reduce((carry, x) => carry + x, 0)
}
</script>

<template>
  <div v-if="item.file" class="event__img" :style="{ backgroundImage: `url(${item.file.url})` }" />
  <div class="show__title">
    <h1 class="event__header">
      <div>
        {{ item.name }}
        <v-btn
          v-if="isAdmin"
          variant="plain"
          icon="$edit"
          :size="42"
          @click="emit('edit', item.id)"
        />
      </div>
      <span class="event__header-date">
        {{ formatDate(item.date) }} {{ formatWeekday(item.date) }}
        <span v-if="item.time" class="event__header-time">
          {{ formatTime(item.time) }}
        </span>
      </span>
    </h1>
  </div>
  <div class="event__desc">
    {{ item.description }}
  </div>
  <div v-if="!isAdmin" class="event__participants">
    <div>
      <span v-if="getParticipantsCount(item) === 0" class="text-gray">
        нет участников
      </span>
      <span v-else>
        {{ getParticipantsCount(item) }} участников
      </span>
    </div>
    <div
      v-if="item.participant"
      class="event__confirmation"
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
      {{ EventParticipantConfirmationLkLabel[item.participant.confirmation] }}
    </div>
  </div>
</template>

<style lang="scss">
.event {
  &__img {
    width: 100%;
    height: 300px;
    background-size: cover;
    background-position: center center;
    margin-bottom: 30px;
  }

  &__header {
    display: flex;
    width: 100%;
    justify-content: space-between;
    & > div {
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }
    .v-btn {
      font-size: 16px;
    }
    span {
      // color: rgb(var(--v-theme-gray));
    }
    .v-chip {
      font-size: 12px;
    }

    &-date {
      position: relative;
      font-size: 24px;
      top: 8px;
    }

    &-time {
      position: absolute;
      top: 22px;
      right: 0px;
      font-size: 60px;
    }
  }

  &__desc {
    width: 70%;
  }

  &__participants {
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 20px;
  }

  &__confirmation {
    .v-icon {
      font-size: 18px !important;
    }
  }
}
</style>
