<script setup lang="ts">
const route = useRoute()

const { $addSseListener, $removeSseListener } = useNuxtApp()
const item = ref<TelegramListResource>()
const deleting = ref(false)
const router = useRouter()

async function loadData() {
  const { data } = await useHttp<TelegramListResource>(
    `telegram-lists/${route.params.id}`,
  )
  if (data.value) {
    item.value = data.value
  }
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить отправку?')) {
    return
  }
  await useHttp(
    `telegram-lists/${item.value?.id}`,
    {
      method: 'delete',
    },
  )
  await router.push({ name: 'telegram-lists' })
}

$addSseListener('TelegramListSentEvent', (data: any) => {
  console.log('TelegramListSentEvent', data)
  loadData()
})

onUnmounted(() => $removeSseListener('TelegramListSentEvent'))

nextTick(loadData)
</script>

<template>
  <v-fade-transition>
    <UiLoader v-if="item === undefined" />
    <div v-else class="show">
      <div class="show__title">
        <h2>
          Рассылка от {{ formatDateTime(item.created_at!) }}
        </h2>
        <v-btn
          v-if="item.status === 'scheduled'"
          icon="$delete"
          :size="48"
          :loading="deleting"
          class="remove-btn"
          @click="destroy()"
        />
      </div>

      <div class="show__content">
        <div>
          <div>Создал</div>
          <div>
            {{ formatName(item.user) }}
          </div>
        </div>
        <div>
          <div> Статус </div>
          <div>
            <TelegramListStatus :item="item" />
          </div>
        </div>
        <div>
          <div>Время</div>
          <div>
            <template v-if="item.scheduled_at">
              запланирована на {{ formatDateTime(item.scheduled_at) }}
            </template>
            <template v-else>
              мгновенная отправка
            </template>
          </div>
        </div>
        <div>
          <div> Кому отправлять </div>
          <div>
            <TelegramListRecipients :item="item" />
          </div>
        </div>
        <div v-if="item.event">
          <div>Событие</div>
          <div>
            <RouterLink :to="`/events/${item.event.id}`">
              {{ item.event.name }}
            </RouterLink>
          </div>
        </div>
        <div>
          <div>Сообщение</div>
          <div style="white-space: pre-wrap;">
            {{ item.text }}
          </div>
          <div v-if="item.event && item.is_confirmable" class="d-flex ga-2 mt-1">
            <v-btn variant="tonal" size="small" :rounded="false">
              подтвердить участие
            </v-btn>
            <v-btn variant="tonal" size="small" :rounded="false">
              отказаться
            </v-btn>
          </div>
        </div>
        <template v-for="(items, key) in item.result">
          <div v-if="items.length" :key="key" class="telegram-list__send-to">
            <h2 class="mb-5">
              {{ SendToLabel[key] }}
            </h2>
            <div class="table table--padding">
              <div v-for="person in items" :key="person.id">
                <div style="width: 300px">
                  <UiPerson :item="person" />
                </div>
                <div>
                  <div
                    v-for="message in person.messages"
                    :key="message.id"
                    :class="{ 'text-secondary': !!message.telegram_id }"
                  >
                    {{ formatPhone(message.number) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </v-fade-transition>
</template>

<style lang="scss">
.telegram-list {
  &__numbers {
    display: flex;
    flex-direction: column;
    gap: 8px;
    & > div {
      display: flex;
      gap: 8px;
    }
  }
  &__send-to {
    margin-top: 30px;
  }
}
</style>
