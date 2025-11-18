<script setup lang="ts">
import type { EventListResource, EventResource } from '.'
import { mdiCheckAll } from '@mdi/js'
import { format, getMonth } from 'date-fns'

const { item } = defineProps<{
  item: EventResource | EventListResource
}>()

function formatDateLocal(date: string) {
  const month = getMonth(date) + 1
  const monthLabel = MonthLabelDative[month as Month]

  return format(date, `d ${monthLabel} yyyy`)
}
</script>

<template>
  <div class="event-header">
    <div>
      <div class="show__title">
        <h1 class="event__header">
          <div>
            {{ item.name }}
          </div>
        </h1>
      </div>
      <div class="event__desc mt-4">
        {{ item.description }}
      </div>

      <div class="mt-4 event__date-time">
        {{ formatDateLocal(item.date) }}
        <span v-if="item.time">
          в {{ formatTime(item.time) }}
        </span>
      </div>

      <slot>
      </slot>
    </div>
    <div>
      <div v-if="item.file" class="event__img" :style="{ backgroundImage: `url(${item.file.url})` }" />
    </div>
  </div>
</template>

<style lang="scss">
.event-header {
  display: flex;
  gap: 20px;
  & > div {
    flex: 1;
  }
}

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

    .v-chip {
      font-size: 12px;
    }
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

  &__date-time {
    font-weight: bold;
  }
}
</style>
