<script setup lang="ts">
import { clone } from 'rambda'
import type { PersonSelectorDialog } from '#build/components'

const emit = defineEmits(['updated'])
const modelDefaults: {
  text: string
  participants: PersonListResource[]
} = {
  text: '',
  participants: [],
}
const { dialog, width } = useDialog('default')
const personSelectorDialog = ref<InstanceType<typeof PersonSelectorDialog>>()
const item = ref(clone(modelDefaults))
const loading = ref(false)

function open() {
  dialog.value = true
  item.value = clone(modelDefaults)
}

async function send() {
  loading.value = true
  await useHttp('telegram-messages', {
    method: 'post',
    body: item.value,
  })
  emit('updated')
  setTimeout(() => (loading.value = false), 300)
  dialog.value = false
}

function onParticipantsSelected(participants: PersonListResource[]) {
  item.value.participants = participants
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        Групповая отправка
      </div>
      <div class="dialog-body">
        <div>
          <v-textarea
            v-model="item.text"
            label="Текст сообщения"
            no-resize
            auto-grow
          />
        </div>
        <div>
          <UiIconLink @click="personSelectorDialog?.open()">
            получатели ({{ item.participants.length }})
          </UiIconLink>
        </div>
        <div class="mt-4">
          <v-btn
            :color=" item.text && item.participants.length ? 'primary' : 'gray'"
            :disabled="!(item.text && item.participants.length)"
            :loading="loading"
            block
            @click="send()"
          >
            отправить
            <span v-if="item.participants.length" class="ml-1 text-gray">
              {{ item.participants.length }}
            </span>
          </v-btn>
        </div>
      </div>
    </div>
  </v-dialog>
  <PersonSelectorDialog
    ref="personSelectorDialog"
    title="Выберите получателей"
    :filters="{
      telegram: 1,
    }"
    @selected="onParticipantsSelected"
  />
</template>
