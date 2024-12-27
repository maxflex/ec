<script setup lang="ts">
import type { TelegramMessageDialog } from '#build/components'
import { mdiHistory, mdiPhone, mdiSend } from '@mdi/js'
import { openCallApp } from '~/components/CallApp'

const telegramMessageDialog = ref<InstanceType<typeof TelegramMessageDialog>>()
const { dialog, width } = useDialog('default')
const item = ref<PhoneResource>()

function open(p: PhoneResource) {
  item.value = p
  dialog.value = true
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div v-if="item" class="dialog-body phone-view pt-5">
        <div>
          <div>
            Телефон:
          </div>
          <div>
            {{ formatPhone(item.number) }}
          </div>
        </div>
        <div>
          <div>
            Комментарий:
          </div>
          <div>
            {{ item.comment || 'не установлено' }}
          </div>
        </div>
        <div>
          <div />
          <div class="phone-view__actions">
            <v-tooltip location="bottom">
              <template #activator="{ props }">
                <v-btn
                  v-bind="props"
                  :size="48"
                  :icon="mdiPhone"
                  variant="text"
                  color="secondary"
                  :href="`tel:${item.number}`"
                />
              </template>
              позвонить
            </v-tooltip>

            <v-tooltip location="bottom">
              <template #activator="{ props }">
                <v-btn :size="48" :icon="mdiHistory" variant="text" color="secondary" v-bind="props" @click="openCallApp(item.number)" />
              </template>
              история вызовов
            </v-tooltip>
            <v-tooltip location="bottom">
              <template #activator="{ props }">
                <v-btn :size="48" :icon="mdiSend" variant="text" color="secondary" v-bind="props" @click="telegramMessageDialog?.open(item)" />
              </template>
              история сообщений
            </v-tooltip>
          </div>
        </div>
      </div>
    </div>
  </v-dialog>
  <TelegramMessageDialog ref="telegramMessageDialog" />
</template>

<style lang="scss">
.phone-view {
  & > div {
    display: flex;
    flex-direction: column;
    gap: 2px;

    & > div {
      &:first-child {
        font-weight: bold;
      }
    }
  }
  &__actions {
    display: flex;
    gap: 12px;
  }
}
</style>
