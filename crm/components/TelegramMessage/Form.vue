<script setup lang="ts">
import { format } from 'date-fns'

const { item } = defineProps<{
  item: TelegramListResource
}>()

const timeMask = { mask: '##:##' }
const scheduledAt = reactive({
  date: '',
  time: '',
})
if (item.scheduled_at) {
  scheduledAt.date = format(item.scheduled_at, 'yyyy-MM-dd')
  scheduledAt.time = format(item.scheduled_at, 'HH:ss')
}
</script>

<template>
  <div class="show__inputs mt-12 telegram-list-form">
    <div>
      <v-textarea
        :model-value="item.text"
        rows="3"
        no-resize
        auto-grow
        label="Текст сообщения"
        disabled
      />
      <v-checkbox
        v-if="item.event"
        disabled
        :model-value="item.is_confirmable"
        label="Запросить подтверждение участия"
        color="secondary"
        class="ml-2"
      />
    </div>
    <div class="double-input">
      <UiDateInput :model-value="scheduledAt.date" disabled />
      <div>
        <v-text-field
          v-maska="timeMask"
          :model-value="scheduledAt.time"
          label="Время"
          disabled
        />
      </div>
    </div>
    <v-btn size="x-large" disabled>
      <div>
        {{ TelegramListStatusLabel[item.status] }}
      </div>
      <div>
        {{ formatName(item.user) }}
      </div>
    </v-btn>
  </div>
</template>

<style lang="scss">
.telegram-list-form {
  position: relative;

  &:after {
    // content: '';
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
    position: absolute;
    cursor: default;
  }

  .v-btn {
    &__content {
      flex-direction: column;
      gap: 2px;

      & > div {
        &:last-child {
          font-size: 10px !important;
        }
      }
    }
  }
}
</style>
