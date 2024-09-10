<script setup lang="ts">
import { mdiSendCircle } from '@mdi/js'

const { dialog, width } = useDialog('default')
const telegramMessages = ref<TelegramMessageResource[]>([])
const input = ref()
const wrapper = ref<HTMLDivElement | null>(null)
const noScroll = ref(false)
const loaded = ref(false)
const phone = ref<PhoneListResource>()
const person = ref<PersonResource>()

function scrollBottom() {
  nextTick(() => {
    console.log(wrapper.value)
    wrapper.value?.scrollTo({
      top: 99999,
      behavior: noScroll.value ? 'instant' : 'smooth',
    })
    input.value.focus()
  })
}

function open(p: PhoneListResource, _person: PersonResource) {
  phone.value = p
  person.value = _person
  dialog.value = true
  loadData()
}

async function loadData() {
  const { data } = await useHttp<ApiResponse<TelegramMessageResource[]>>(
    'telegram-messages',
    {
      params: {
        phone_id: phone.value?.id,
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
  <v-dialog v-model="dialog" :width="width">
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

      <div v-if="person && phone" class="telegram-messages__header">
        <span>
          <v-icon :icon="mdiSendCircle" color="#24A1DE" />
          <b>
            {{ formatName(person) }}
          </b>
          ({{ EntityTypeLabel[phone.entity_type] }})
        </span>
        <span class="text-gray">
          {{ formatPhone(phone.number) }}
        </span>
      </div>

      <transition-group
        name="new-telegram"
        class="telegram-messages__items"
        tag="div"
      >
        <div v-for="m in telegramMessages" :key="m.id" class="telegram-message">
          <UiAvatar :item="m.user || m.phone.entity" :size="46" />
          <div>
            <div class="telegram-message__title">
              <span>
                {{ formatName(m.user || m.phone.entity) }}
              </span>
              <span v-if="m.created_at">
                {{ formatDateTime(m.created_at) }}
              </span>
            </div>
            {{ m.text }}
          </div>
        </div>
      </transition-group>
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
    padding: $padding;
    & > div {
      margin-bottom: 16px;
    }
  }
  &__input {
    display: flex;
    z-index: 1;
    border-top: 1px solid #e0e0e0;
    padding: $padding;
    // min-height: 65px;
    // height: 65px;
    // max-height: 65px;
    position: sticky;
    bottom: 0;
    background: white;
    .v-btn {
      position: relative;
      top: 4px;
    }
    & textarea {
      max-height: 100px;
      // transition: all linear 0.075s;
      overflow-y: auto;
      scrollbar-width: none; /** ff */
      -ms-overflow-style: none; /** ie */
      font-weight: 400;
      line-height: 18px !important;
      top: 2px;
      position: relative;
      padding: 15px 8px 0 !important;
      &::-webkit-scrollbar {
        width: 0; /** webkit */
      }
    }

    .v-field__outline {
      display: none !important;
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
    &--loaded {
      .new-telegram {
        &-enter-active,
        &-leave-active {
          transition: all 100ms linear;
        }
        &-enter-from,
        &-leave-to {
          opacity: 0;
          transform: translateY(20px);
        }
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
    // margin-bottom: 2px;
    & > *:not(:last-child) {
      margin-right: 6px;
    }
    & > span {
      &:first-child {
        font-weight: bold;
      }
      &:not(:first-child) {
        color: rgb(var(--v-theme-gray));
        font-size: 12px;
        font-weight: normal;
      }
    }
    &:hover {
      .telegram-item__controls {
        opacity: 1;
      }
    }
  }
}
.telegram-btn {
  &-enter-active {
    transition: all 0.3s linear;
    transform-origin: center center;
  }
  &-leave-active {
    transition: none !important;
  }
  &-enter-from {
    opacity: 0;
    // transform: scale(0.5);
    transform: translateX(-10px);
  }
}
</style>
