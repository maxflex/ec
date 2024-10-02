<script setup lang="ts">
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
          v-if="!item.is_sent"
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
          <div> Отправить </div>
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
                  <UiPersonLink :item="p" :type="key" />
                </div>
                <div style="width: 200px">
                  <template v-if="item?.results">
                    <span v-if="item.results[`${key}-${p.id}`].length === 0" class="text-error">
                      не доставлено
                    </span>
                    <div class="telegram-list__numbers">
                      <div v-for="number in item.results[`${key}-${p.id}`]" :key="number">
                        <span>доставлено</span>
                        <a :href="`tel:${number}`">
                          {{ formatPhone(number as string) }}
                        </a>
                      </div>
                    </div>
                  </template>
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
}
</style>
