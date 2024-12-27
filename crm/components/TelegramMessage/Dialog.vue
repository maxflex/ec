<script setup lang="ts">
import { mdiAlertCircleOutline, mdiCheckAll } from '@mdi/js'

const { dialog, width, transition } = useDialog('default')
const telegramMessages = ref<TelegramMessageResource[]>([])
const wrapper = ref<HTMLDivElement | null>(null)
const loaded = ref(false)
const phone = ref<PhoneResource>()

function scrollBottom() {
  nextTick(() => {
    console.log(wrapper.value)
    wrapper.value?.scrollTo({
      top: 99999,
      behavior: 'instant',
    })
  })
}

function open(p: PhoneResource) {
  phone.value = p
  dialog.value = true
  loadData()
}

async function loadData() {
  loaded.value = false
  const { data } = await useHttp<ApiResponse<TelegramMessageResource>>(
    'telegram-messages',
    {
      params: {
        number: phone.value!.number,
      },
    },
  )
  if (data.value) {
    telegramMessages.value = data.value.data
    scrollBottom()
    setTimeout(() => {
      loaded.value = true
    }, 200)
  }
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width" :transition="transition">
    <div
      ref="wrapper"
      class="dialog-wrapper telegram-messages-wrapper"
    >
      <v-fade-transition>
        <UiLoader v-if="!loaded" />
      </v-fade-transition>

      <!--      <div v-if="phone" class="telegram-messages__header"> -->
      <!--        <span> -->
      <!--          <v-icon :icon="mdiSendCircle" color="#24A1DE" /> -->
      <!--          <b> -->
      <!--            {{ EntityTypeLabel[phone.entity_type] }} {{ phone.entity_id }} -->
      <!--          </b> -->
      <!--        </span> -->
      <!--        <span class="text-gray"> -->
      <!--          {{ formatPhone(phone.number) }} -->
      <!--        </span> -->
      <!--      </div> -->

      <UiNoData v-if="loaded && telegramMessages.length === 0" />

      <div class="telegram-messages__items">
        <div v-for="m in telegramMessages" :key="m.id" class="telegram-message">
          <div class="telegram-message__title">
            {{ formatDateTime(m.created_at) }}
            <v-icon v-if="m.telegram_id" color="success" :icon="mdiCheckAll" :size="14" />
            <v-icon v-else color="error" :icon="mdiAlertCircleOutline" :size="14" />
          </div>
          <div class="telegram-message__text" v-html="m.text" />
        </div>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.telegram-messages {
  &__items {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    gap: 40px;
    padding: 20px;
    & > div {
      margin-bottom: 16px;
    }
  }
  &-wrapper {
    .loaderr {
      position: absolute;
      z-index: 99;
    }
  }
}
.telegram-message {
  &__title {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 14px;
    margin-bottom: 20px;
    color: rgb(var(--v-theme-gray));
    font-weight: 500;
  }
  &__text {
    white-space: pre-line;
  }
}
</style>
