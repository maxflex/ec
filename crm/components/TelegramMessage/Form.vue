<script setup lang="ts">
import type { TelegramListResource } from '../TelegramList'
import { format } from 'date-fns'

const { item } = defineProps<{ item: TelegramListResource }>()

const router = useRouter()
const destroying = ref(false)
const timeMask = { mask: '##:##' }
const scheduledAt = reactive({
  date: '',
  time: '',
})
if (item.scheduled_at) {
  scheduledAt.date = format(item.scheduled_at, 'yyyy-MM-dd')
  scheduledAt.time = format(item.scheduled_at, 'HH:ss')
}

async function destroy() {
  destroying.value = true
  await useHttp(
    `telegram-lists/${item.id}`,
    {
      method: 'DELETE',
    },
  )

  router.push({ name: 'telegram-lists' })
  useGlobalMessage('рассылка удалена', 'success')
}
</script>

<template>
  <div class="show__inputs mt-12 telegram-list-form">
    <div v-if="item.event" class="relative">
      <v-select label="Событие" :model-value="item.event.name" disabled />
      <UiUnderInput>
        <RouterLink v-if="item.event" :to="`/events/${item.event.id}`">
          перейти в событие
        </RouterLink>
      </UiUnderInput>
    </div>
    <div class="relative">
      <v-textarea
        :model-value="item.text"
        rows="3"
        no-resize
        auto-grow
        label="Текст сообщения"
        disabled
      />
      <TelegramMessagePreview :text="item.text" />
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
        <template v-if="item.status === 'sent'">
          {{ formatDateTime(item.scheduled_at || item.created_at!) }}
        </template>
      </div>
      <div>
        {{ formatName(item.user!) }}
      </div>
    </v-btn>
    <v-btn
      v-if="item.status === 'scheduled'" size="x-large"
      color="error"
      variant="outlined"
      :loading="destroying"
      @click="destroy()"
    >
      отменить рассылку
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
