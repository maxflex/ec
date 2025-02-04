<script setup lang="ts">
import {
  mdiEmailOffOutline,
  mdiPhone,
  mdiSendCircle,
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

function open(p: PhoneResource) {
  item.value = p
  dialog.value = true
  loadCalls()
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
}

watch(selectedTab, (newVal) => {
  if (newVal === 'telegramMessages') {
    loadTelegramMessages()
  }
})

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div v-if="item" class="dialog-wrapper phone-dialog">
      <div class="dialog-header">
        <div class="d-flex ga-2 align-center">
          {{ formatPhone(item.number) }}
          <div v-if="item.comment" class="dialog-subheader">
            {{ item.comment }}
          </div>
        </div>
        <div>
          <v-btn v-if="item.telegram_id" color="secondary" :icon="mdiSendCircle" :size="48" variant="text" class="no-pointer-events" style="transform: scale(1.1)" />
          <v-btn v-if="item.is_telegram_disabled" color="error" :icon="mdiEmailOffOutline" :size="48" variant="text" class="no-pointer-events" />
          <!--          <v-btn -->
          <!--            :size="48" -->
          <!--            variant="text" -->
          <!--            :disabled="!item.telegram_id" -->
          <!--            icon="$send" -->
          <!--            class="no-pointer-events" -->
          <!--          /> -->
          <!--          <v-btn -->
          <!--            :icon="item.is_telegram_disabled ? mdiEmailOffOutline : mdiEmailOutline" -->
          <!--            :color="item.is_telegram_disabled ? 'error' : 'black'" -->
          <!--            :size="48" -->
          <!--            variant="text" -->
          <!--            class="no-pointer-events" -->
          <!--          /> -->
          <v-btn
            :size="48"
            :icon="mdiPhone"
            variant="text"
            color="black"
            :href="`tel:${item.number}`"
          />
        </div>
      </div>
      <div class="dialog-body pa-0 ga-0">
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
        </template>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.phone-dialog {
  .tabs {
    position: sticky;
    top: 0;
    z-index: 1;
    min-height: min-content;
    background: white;
  }
}
</style>
