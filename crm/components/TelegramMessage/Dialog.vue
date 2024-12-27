<script setup lang="ts">
import { mdiAlertCircleOutline, mdiCheckAll } from '@mdi/js'

const { dialog, width, transition } = useDialog('default')
const telegramMessages = ref<TelegramMessageResource[]>([])
const wrapper = ref<HTMLDivElement | null>(null)
const noScroll = ref(false)
const loaded = ref(false)
const phone = ref<PhoneResource>()

function scrollBottom() {
  nextTick(() => {
    console.log(wrapper.value)
    wrapper.value?.scrollTo({
      top: 99999,
      behavior: noScroll.value ? 'instant' : 'smooth',
    })
  })
}

function open(p: PhoneResource) {
  phone.value = p
  dialog.value = true
  loadData()
}

async function loadData() {
  const { data } = await useHttp<ApiResponse<TelegramMessageResource>>(
    'telegram-messages',
    {
      params: {
        number: phone.value!.number,
      },
    },
  )
  if (data.value) {
    noScroll.value = true
    telegramMessages.value = data.value.data
    scrollBottom()
    setTimeout(() => {
      noScroll.value = false
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
      :class="{
        'telegram-messages-wrapper--no-scroll': noScroll,
        'telegram-messages-wrapper--loaded': loaded,
      }"
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
          <div>
            <div class="telegram-message__title">
              {{ formatDateTime(m.created_at) }}
              <v-icon v-if="m.telegram_id" color="success" :icon="mdiCheckAll" :size="14" />
              <v-icon v-else color="error" :icon="mdiAlertCircleOutline" :size="14" />
            </div>
            <div class="telegram-message__text" v-html="m.text" />
          </div>
        </div>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.telegram-messages {
  $padding: 10px 16px;
  &__header {
    padding-left: 16px;
    padding-right: 16px;
    background: #eaf8ff;
    position: sticky;
    top: 0;
    z-index: 1;
    // border-bottom: 1px solid #e0e0e0;
    border-bottom: 1px solid #a4cde2;
    display: flex;
    justify-content: space-between;
    min-height: 40px;
    & > span {
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 8px;
      .v-icon {
        font-size: 24px;
      }
    }
  }
  &__items {
    flex: 1;
    // height: 0px; /*here the height is set to 0px*/
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    gap: 40px;
    padding: $padding;
    & > div {
      margin-bottom: 16px;
    }
  }
  &-wrapper {
    .loaderr {
      position: absolute;
      z-index: 10;
    }
    &--no-scroll {
      &::-webkit-scrollbar {
        display: none;
      }
    }
  }
}
.telegram-message {
  display: flex;
  align-items: flex-start;
  font-size: 14px;
  & > div:last-child {
    flex: 1;
  }
  .v-avatar {
    margin-right: 16px;
    margin-top: 1px;
  }
  &__title {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    font-size: 16px;
    margin-bottom: 20px;
    color: rgb(var(--v-theme-gray));
    font-weight: 500;
  }
  &__text {
    white-space: pre-line;
  }
}
</style>
