<script setup lang="ts">
const route = useRoute()

const { $addSseListener, $removeSseListener } = useNuxtApp()
const item = ref<TelegramListResource>()
const isExpanded = ref<boolean>(false)

async function loadData() {
  const { data } = await useHttp<TelegramListResource>(
    `telegram-lists/${route.params.id}`,
  )
  if (data.value) {
    item.value = data.value
  }
}

const maxRows = computed<number>(() => {
  if (!item.value) {
    return 0
  }
  return Math.max(...Object.values(item.value.result).map(e => e.length))
})

const maxDisplayedRows = computed<number>(() => {
  if (isExpanded.value) {
    return maxRows.value
  }
  return 3
})

function isSelected(key: SendTo): boolean {
  if (!item.value) {
    return false
  }
  return item.value.send_to.includes(key)
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
    <div v-else class="show pt-0">
      <div class="show__title-new">
        <h2>
          Рассылка от {{ formatDateTime(item.created_at!) }}
        </h2>
        <RouterLink v-if="item.event" :to="`/events/${item.event.id}`">
          {{ item.event.name }}
        </RouterLink>
      </div>

      <div class="show__content">
        <div>
          <div></div>
          <v-table class="send-to-table">
            <thead>
              <tr>
                <th v-for="(label, key) in SendToLabel" :key="key">
                  <div>
                    <UiCheckbox :value="isSelected(key)" disabled />
                    <span>
                      {{ label }}
                    </span>
                  </div>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="i in maxDisplayedRows" :key="i">
                <td v-for="(label, key) in SendToLabel" :key="key">
                  <div v-if="item.result[key] && i <= item.result[key].length" class="send-to-table__content">
                    <UiPerson :item="item.result[key][i - 1]" />
                    <div class="send-to-table__phones">
                      <div
                        v-for="message in item.result[key][i - 1].messages"
                        :key="message.id"
                        :class="{ 'text-secondary': !!message.telegram_id }"
                      >
                        {{ formatPhone(message.number) }}
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
              <tr v-if="maxDisplayedRows < maxRows" class="cursor-pointer" @click="isExpanded = true">
                <td colspan="100">
                  <a>
                    показать все {{ maxRows }}
                  </a>
                </td>
              </tr>
            </tbody>
          </v-table>
        </div>
      </div>

      <TelegramMessageForm :item="item" />
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
