<script setup lang="ts">
import type { TelegramListResource } from '~/components/TelegramList'

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
      <div class="show__title-new pb-0">
        <h2>
          Рассылка от {{ formatDateTime(item.created_at!) }}
        </h2>
      </div>

      <div class="show__content">
        <div>
          <div></div>
          <v-table class="send-to-table">
            <thead>
              <tr>
                <th>
                  <div class="d-flex flex-column pb-4">
                    <div>
                      <UiCheckbox :value="isSelected('students')" disabled />
                      <span>
                        {{ SendToLabel.students }}
                      </span>
                    </div>
                    <div>
                      <UiCheckbox :value="isSelected('representatives')" disabled />
                      <span>
                        {{ SendToLabel.representatives }}
                      </span>
                    </div>
                  </div>
                </th>
                <th>
                  <div>
                    <UiCheckbox :value="isSelected('teachers')" disabled />
                    <span>
                      {{ SendToLabel.teachers }}
                    </span>
                  </div>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="i in maxDisplayedRows" :key="i">
                <td>
                  <div class="control-lk__item">
                    <div
                      v-if="i <= item.result.students.length"
                      class="control-lk__item-client"
                      :class="{ 'send-to-table__hidden': !isSelected('students') }"
                    >
                      <div class="control-lk__name">
                        <UiPerson :item="item.result.students[i - 1]" />
                      </div>
                      <div class="control-lk__phones">
                        <div
                          v-for="message in item.result.students[i - 1].messages"
                          :key="message.id"
                          :class="{ 'text-secondary': !!message.telegram_id }"
                        >
                          {{ formatPhone(message.number) }}
                        </div>
                      </div>
                      <div class="control-lk__directions">
                        <ClientDirections :items="item.recipients.students[i - 1].directions" />
                      </div>
                    </div>
                    <div
                      v-if="i <= item.result.representatives.length"
                      class="control-lk__item-representative"
                      :class="{ 'send-to-table__hidden': !isSelected('representatives') }"
                    >
                      <div class="control-lk__name">
                        <UiPerson :item="item.result.representatives[i - 1]" />
                      </div>
                      <div class="control-lk__phones">
                        <div
                          v-for="message in item.result.representatives[i - 1].messages"
                          :key="message.id"
                          :class="{ 'text-secondary': !!message.telegram_id }"
                        >
                          {{ formatPhone(message.number) }}
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
                <!-- teacher -->
                <td>
                  <div v-if="i <= item.result.teachers.length" class="control-lk__item">
                    <div class="control-lk__item-client" :class="{ 'send-to-table__hidden': !isSelected('teachers') }">
                      <div class="control-lk__name">
                        <UiPerson :item="item.result.teachers[i - 1]" />
                      </div>
                      <div class="control-lk__phones">
                        <div
                          v-for="message in item.result.teachers[i - 1].messages"
                          :key="message.id"
                          :class="{ 'text-secondary': !!message.telegram_id }"
                        >
                          {{ formatPhone(message.number) }}
                        </div>
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
