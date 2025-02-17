<script setup lang="ts">
import {
  mdiPhone,
  mdiSendVariant,
} from '@mdi/js'

const { dialog, width } = useDialog('default')
const item = ref<PhoneResource>()
const callsList = ref<CallListResource[]>([])
const telegramMessages = ref<TelegramMessageResource[]>([])
const tabs = {
  calls: 'история звонков',
  telegramMessages: 'история сообщений',
} as const
const selectedTab = ref<keyof typeof tabs>('calls')
const loading = ref(true)
const wrapper = ref<HTMLDivElement | null>(null)
const text = ref('')
const input = ref<HTMLInputElement | null>(null)
const sending = ref(false)

function open(p: PhoneResource) {
  item.value = p
  dialog.value = true
  loadCalls()
}

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
        phone_id: item.value?.id,
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
  const { data } = await useHttp<ApiResponse<CallListResource>>(`calls`, {
    params: {
      q: item.value!.number,
    },
  })
  callsList.value = data.value!.data
  loading.value = false
}

async function loadTelegramMessages() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<TelegramMessageResource>>(
    `telegram-messages`,
    {
      params: {
        number: item.value!.number,
      },
    },
  )
  telegramMessages.value = data.value!.data
  loading.value = false
  scrollBottom()
}

watch(selectedTab, (newVal) => {
  if (newVal === 'telegramMessages') {
    loadTelegramMessages()
  }
})

function scrollBottom() {
  nextTick(() => {
    console.log(wrapper.value)
    wrapper.value?.scrollTo({
      top: 99999,
      behavior: 'instant',
    })
  })
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div v-if="item" class="dialog-wrapper phone-dialog">
      <div class="dialog-header">
        <div class="d-flex ga-2 align-center">
          <PhoneNumber style="white-space: nowrap;" :item="item" />
          <div v-if="item.comment" class="dialog-subheader px-3">
            {{ filterTruncate(item.comment, 55) }}
          </div>
        </div>
        <div>
          <v-btn
            :size="48"
            :icon="mdiPhone"
            variant="text"
            color="black"
            :href="`tel:${item.number}`"
          />
        </div>
      </div>
      <div ref="wrapper" class="dialog-body pa-0 ga-0">
        <div class="tabs">
          <div
            v-for="(label, key) in tabs"
            :key="key"
            class="tabs-item"
            :class="{ 'tabs-item--active': selectedTab === key }"
            @click="selectedTab = key"
          >
            {{ label }}
          </div>
        </div>
        <UiLoader v-if="loading" />
        <template v-else-if="selectedTab === 'calls'">
          <UiNoData v-if="callsList.length === 0" />
          <CallAppCallsList v-else :items="callsList" />
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
  </v-dialog>
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
}
</style>
