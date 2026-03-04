<script setup lang="ts">
import type { CallListResource } from '../CallApp'
import { mdiClipboardCheckOutline, mdiClipboardOutline, mdiContentCopy, mdiFileMultipleOutline, mdiPhone, mdiSendVariant } from '@mdi/js'
import { cloneDeep } from 'lodash-es'

const item = ref<PhoneResource>()
const { dialog, width } = useDialog('large')

const { tabs, selectedTab, tabCounts } = useTabs({
  calls: 'история звонков',
  telegramMessages: 'история telegram',
})
const callsList = ref<CallListResource[]>([])
const telegramMessages = ref<TelegramMessageResource[]>([])
// const smsMessages = ref<SmsMessageListResource[]>([])
const loading = ref(true)
const wrapper = ref<HTMLDivElement | null>(null)
const text = ref('')
const input = ref<HTMLInputElement | null>(null)
const sending = ref(false)
const copied = ref(false)
const person = ref<PersonResource>()

type Tab = keyof typeof tabs

type TabLoaded = Record<Tab, boolean>

const tabLoadedDefaults = {
  calls: true, // первая вкладка загружается всегда
  telegramMessages: false,
}

// если вкладка была загружена, второй раз не загружаем
const tabLoaded = ref<TabLoaded>(cloneDeep(tabLoadedDefaults))

function open(p: PhoneResource, _person?: PersonResource) {
  item.value = p
  person.value = _person
  selectedTab.value = 'calls'
  tabLoaded.value = cloneDeep(tabLoadedDefaults)
  loadCalls()
  dialog.value = true
}

async function loadCalls() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<CallListResource>>(
    `calls`,
    { params: { number: item.value!.number } },
  )
  callsList.value = data.value!.data
  loading.value = false
}

async function loadTelegramMessages() {
  if (tabLoaded.value.telegramMessages) {
    scrollBottom()
    return
  }

  loading.value = true
  const { data } = await useHttp<ApiResponse<TelegramMessageResource>>(
    `telegram-messages`,
    { params: { number: item.value!.number } },
  )
  telegramMessages.value = data.value!.data
  loading.value = false
  tabLoaded.value.telegramMessages = true
  scrollBottom()
}

function scrollBottom() {
  nextTick(() => {
    wrapper.value?.scrollTo({
      top: 99999,
      behavior: 'instant',
    })
  })
}

function copyToClipboard() {
  copied.value = true
  navigator.clipboard.writeText(item.value!.number)
}

watch(selectedTab, (newVal: Tab) => {
  switch (newVal) {
    case 'calls':
      smoothScroll('dialog', 'top', 'instant')
      break

    case 'telegramMessages':
      loadTelegramMessages()
      break

    // case 'smsMessages':
    //   loadSmsMessages()
    //   break
  }
})

async function send() {
  if (!text.value.length) {
    return
  }
  sending.value = true
  const { data } = await useHttp<TelegramMessageResource>(
    `telegram-messages`,
    {
      method: 'post',
      body: {
        text: text.value,
        phone_id: item.value!.id,
      },
    },
  )
  if (data.value) {
    telegramMessages.value.push(data.value)
    text.value = ''
    scrollBottom()
  }
  sending.value = false
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width" class="dialog-fullwidth">
    <div v-if="item" class="panel">
      <div class="panel-info">
        <div>
          <!-- <h2
            style="font-size: 28px"
            class="nowrap pt-1"
            :class="{
              'text-secondary': !!item.telegram_id,
              'text-error': item.is_telegram_disabled,
            }"
          >
            {{ formatPhone(item.number) }}
          </h2> -->
          <h2 style="font-size: 28px" class="nowrap pt-1">
            {{ formatPhone(item.number) }}
          </h2>
        </div>
        <div>
          <div>
            {{ EntityTypeLabel[item.entity_type] }}
          </div>
          <div>
            <UiPerson v-if="person" :item="person" no-link />
            <template v-else>
              {{ item.entity_id }}
            </template>
          </div>
        </div>
        <div v-if="item.entity_type !== EntityTypeValue.request">
          <div>
            Telegram
          </div>
          <div>
            <UiIfSet :value="!!item.telegram_id">
              установлен <template v-if="item.is_telegram_disabled">
                / <span class="text-error">выкл.</span>
              </template>
              <template #empty>
                не установлен
              </template>
            </UiIfSet>
          </div>
        </div>
        <!-- <div>
          <div>
            Тип пользователя
          </div>
          <div>
            {{ EntityTypeLabel[item.entity_type] }}
          </div>
        </div> -->
        <div>
          <div>
            Комментарий
          </div>
          <div>
            <UiIfSet :value="!!item.comment">
              {{ item.comment }}
              <template #empty>
                не установлен
              </template>
            </UiIfSet>
          </div>
        </div>
        <div class="panel-actions">
          <PhonePreviewModeBtn :item="item" />
          <v-tooltip location="bottom">
            <template #activator="{ props }">
              <v-btn
                v-bind="props"
                :size="48"
                :icon="copied ? mdiClipboardCheckOutline : mdiClipboardOutline"
                variant="plain"
                :disabled="copied"
                @click="copyToClipboard()"
              />
            </template>
            скопировать номер
          </v-tooltip>
          <v-tooltip location="bottom">
            <template #activator="{ props }">
              <v-btn
                v-bind="props"
                :size="48"
                :icon="mdiPhone"
                variant="plain"
                color="gray"
                :href="`tel:${item.number}`"
              />
            </template>
            позвонить
          </v-tooltip>
          <v-btn
            icon="$close"
            :size="48"
            variant="plain"
            @click="dialog = false"
          />
        </div>
      </div>
      <UiTabs v-model="selectedTab" :items="tabs" :counts="tabCounts">
      </UiTabs>
    </div>
    <div
      ref="wrapper"
      :key="item ? item.id : 'null'"
      class="dialog-body pa-0 ga-0"
    >
      <UiLoader v-if="loading" />
      <template v-if="selectedTab === 'calls'">
        <UiNoData v-if="callsList.length === 0" />
        <CallList v-else class="px-5" :items="callsList" />
      </template>
      <template v-else>
        <UiNoData v-if="telegramMessages.length === 0" />
        <TelegramMessageHistoryList :items="telegramMessages" />
        <div v-if="item && item.telegram_id && !item.is_telegram_disabled" class="comments__input">
          <v-textarea
            ref="input"
            v-model="text"
            rows="1"
            placeholder="Введите сообщение..."
            auto-grow
            hide-details
            maxlength="1000"
            max-height="100"
            :disabled="sending"
            @keydown.enter.exact.prevent
            @keyup.enter.exact="send()"
          />
          <transition name="comment-btn">
            <v-btn
              v-if="text.length > 0"
              :icon="mdiSendVariant"
              height="48"
              width="48"
              variant="text"
              color="secondary"
              :loading="sending"
              @click="send()"
            />
          </transition>
        </div>
      </template>
    </div>
  </v-dialog>
</template>
