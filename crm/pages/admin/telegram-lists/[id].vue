<script setup lang="ts">
import { mdiEmailOffOutline } from '@mdi/js'

const route = useRoute()

const { $addSseListener, $removeSseListener } = useNuxtApp()
const item = ref<TelegramListResource>()
const deleting = ref(false)
const router = useRouter()

async function loadData() {
  const { data } = await useHttp<TelegramListResource>(`telegram-lists/${route.params.id}`)
  if (data.value) {
    item.value = data.value
  }
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить отправку?')) {
    return
  }
  await useHttp(`telegram-lists/${item.value?.id}`, {
    method: 'delete',
  })
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
          Отправка от {{ formatDateTime(item.created_at!) }}
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
          <div> {{ SendToLabel[item.send_to] }} </div>
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
          <div>{{ item.text }}</div>
          <div v-if="item.event && item.is_confirmable" class="d-flex ga-2 mt-1">
            <v-btn variant="tonal" size="small" :rounded="false">
              подтвердить участие
            </v-btn>
            <v-btn variant="tonal" size="small" :rounded="false">
              отказаться
            </v-btn>
          </div>
        </div>
        <template v-for="(people, key) in item.recipients">
          <div v-if="people.length" :key="key">
            <div>
              {{ key === 'clients' ? 'Клиенты' : 'Преподаватели' }}
            </div>
            <div class="table table--padding">
              <div v-for="p in people" :key="p.id">
                <div style="width: 380px">
                  <UiPerson :item="p" />
                </div>
                <template v-if="item?.results">
                  <div style="width: 250px">
                    <div
                      v-if="key === 'clients' && item.send_to === 'parents'"
                      :class="{
                        'opacity-5': item.status !== 'sent',
                      }"
                      class="text-gray"
                    >
                      не отправлялось
                    </div>
                    <template v-else>
                      <div
                        v-for="r in item.results[`${key}-${p.id}`].filter(e => !e.is_parent)"
                        :key="r.id"
                        :class="{
                          'opacity-5': item.status !== 'sent',
                          'text-success': r.is_sent,
                          'text-error': !r.is_sent,
                        }"
                      >
                        {{ formatPhone(r.number) }}
                        <v-icon v-if="r.is_telegram_disabled" :icon="mdiEmailOffOutline" :size="14" color="error" class="vfn-1" />
                      </div>
                    </template>
                  </div>
                  <div v-if="key === 'clients'">
                    <div
                      v-if="item.send_to === 'students'"
                      class="text-gray"
                      :class="{
                        'opacity-5': item.status !== 'sent',
                      }"
                    >
                      не отправлялось
                    </div>
                    <template v-else>
                      <div
                        v-for="r in item.results[`${key}-${p.id}`].filter(e => e.is_parent)"
                        :key="r.id"
                        :class="{
                          'opacity-5': item.status !== 'sent',
                          'text-success': r.is_sent,
                          'text-error': !r.is_sent,
                        }"
                      >
                        {{ formatPhone(r.number) }}
                        <v-icon v-if="r.is_telegram_disabled" :icon="mdiEmailOffOutline" :size="14" color="error" class="vfn-1" />
                      </div>
                    </template>
                  </div>
                </template>
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
}
</style>
