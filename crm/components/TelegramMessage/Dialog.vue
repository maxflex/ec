<script setup lang="ts">
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

function openWithTelegramMessages(messages: TelegramMessageResource[]) {
  telegramMessages.value = messages
  loaded.value = true
  dialog.value = true
  scrollBottom()
}

async function loadData() {
  loaded.value = false
  const { data } = await useHttp<ApiResponse<TelegramMessageResource>>(
    `telegram-messages`,
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

defineExpose({ open, openWithTelegramMessages })
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
      <UiNoData v-if="loaded && telegramMessages.length === 0" />
      <TelegramMessageHistoryList :items="telegramMessages" />
    </div>
  </v-dialog>
</template>

<style lang="scss">
.telegram-messages {
  &-wrapper {
    .loaderr {
      position: absolute;
      z-index: 99;
    }
  }
}
</style>
