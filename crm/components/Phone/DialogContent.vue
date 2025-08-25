<script setup lang="ts">
import type { CallListResource } from '../CallApp'
import type { SmsMessageListResource } from '../SmsMessage'

import {
  mdiContentCopy,
  mdiPhone,
  mdiSendVariant,
} from '@mdi/js'

const { item } = defineProps<{
  item: PhoneResource
  backBtn?: boolean
}>()
const emit = defineEmits<{ back: [] }>()

const { number } = item
const copied = ref(false)

const { tabs, selectedTab } = useTabs({
  calls: 'история звонков',
  telegramMessages: 'история telegram',
  smsMessages: 'история sms',
})
type Tab = keyof typeof tabs

const callsList = ref<CallListResource[]>([])
const telegramMessages = ref<TelegramMessageResource[]>([])
const smsMessages = ref<SmsMessageListResource[]>([])
const loading = ref(true)
const wrapper = ref<HTMLDivElement | null>(null)
const text = ref('')
const input = ref<HTMLInputElement | null>(null)
const sending = ref(false)

// если вкладка была загружена, второй раз не загружаем
const tabLoaded = ref<Record<Tab, boolean>>({
  calls: true, // первая вкладка загружается всегда
  telegramMessages: false,
  smsMessages: false,
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
        phone_id: item.id,
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

async function loadCalls() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<CallListResource>>(
    `calls`,
    { params: { number } },
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
    { params: { number } },
  )
  telegramMessages.value = data.value!.data
  loading.value = false
  tabLoaded.value.telegramMessages = true
  scrollBottom()
}

async function loadSmsMessages() {
  if (tabLoaded.value.smsMessages) {
    scrollBottom()
    return
  }

  loading.value = true
  const { data } = await useHttp<ApiResponse<SmsMessageListResource>>(
    `sms-messages`,
    { params: { number } },
  )
  smsMessages.value = data.value!.data
  loading.value = false
  tabLoaded.value.smsMessages = true
  scrollBottom()
}

watch(selectedTab, (newVal) => {
  switch (newVal) {
    case 'calls':
      smoothScroll('dialog', 'top', 'instant')
      break

    case 'telegramMessages':
      loadTelegramMessages()
      break

    case 'smsMessages':
      loadSmsMessages()
      break
  }
})

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
  navigator.clipboard.writeText(item.number)
}

nextTick(loadCalls)
</script>

<template>
  <div class="dialog-wrapper phone-dialog" :class="{ 'phone-dialog--has-back-btn': backBtn }">
    <div class="dialog-header">
      <div class="d-flex ga-2 align-center">
        <v-btn v-if="backBtn" icon="$back" :size="48" variant="plain" @click="emit('back')" />
        <PhoneNumber style="white-space: nowrap;" :item="item" />
        <div v-if="item.comment" class="dialog-subheader px-3">
          {{ filterTruncate(item.comment, 55) }}
        </div>
      </div>
      <div>
        <v-btn
          :size="48"
          :icon="mdiContentCopy"
          variant="text"
          color="black"
          :disabled="copied"
          @click="copyToClipboard()"
        />
        <v-btn
          :size="48"
          :icon="mdiPhone"
          variant="text"
          color="black"
          :href="`tel:${item.number}`"
        />
      </div>
    </div>
    <div ref="wrapper" :key="item.id" class="dialog-body pa-0 ga-0">
      <UiTabs v-model="selectedTab" :items="tabs" />

      <UiLoader v-if="loading" :offset="50" />
      <template v-else-if="selectedTab === 'calls'">
        <UiNoData v-if="callsList.length === 0" />
        <CallAppCallsList v-else :items="callsList" />
      </template>

      <template v-else-if="selectedTab === 'smsMessages'">
        <UiNoData v-if="smsMessages.length === 0" />
        <SmsMessageHistoryList :items="smsMessages" />
      </template>
      <template v-else>
        <UiNoData v-if="telegramMessages.length === 0" />
        <TelegramMessageHistoryList :items="telegramMessages" />
        <div class="comments__input">
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
  </div>
</template>

<style lang="scss">
.phone-dialog {
  .dialog-header {
    background: white !important;
  }
  .tabs {
    position: sticky;
    top: 0;
    z-index: 1;
    min-height: min-content;
    background: white;
  }

  &--has-back-btn {
    .dialog-header {
      padding-left: 8px !important;
    }
  }
}
</style>
