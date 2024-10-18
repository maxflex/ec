<script setup lang="ts">
import { mdiHistory, mdiPhone, mdiSend } from '@mdi/js'
import type { TelegramMessageDialog } from '#build/components'
import { openCallApp } from '~/components/CallApp'

const telegramMessageDialog = ref<InstanceType<typeof TelegramMessageDialog>>()
const { dialog, width } = useDialog('default')
const item = ref<PhoneListResource>()

function open(p: PhoneListResource) {
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
            <v-btn
              :size="48"
              :icon="mdiPhone"
              variant="text"
              color="secondary"
              :href="`tel:${item.number}`"
            />
            <v-btn :size="48" :icon="mdiHistory" variant="text" color="secondary" @click="openCallApp(item.number)" />
            <v-btn :size="48" :icon="mdiSend" variant="text" color="secondary" @click="telegramMessageDialog?.open(item)" />
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
