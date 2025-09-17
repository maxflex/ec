<script setup lang="ts">
import { VDialogTransition } from 'vuetify/components'

const emit = defineEmits<{
  saved: [name: string]
}>()

const dialog = ref(false)
const saving = ref(false)
const textInput = ref()
const name = ref('')

function open() {
  dialog.value = true
  setTimeout(() => textInput.value.focus(), 50)
}

async function save() {
  emit('saved', name.value)
  dialog.value = false
}

defineExpose({ open })
</script>

<template>
  <v-dialog
    v-model="dialog"
    max-width="500"
    location="center"
    :content-class="null"
    :transition="{ component: VDialogTransition }"
  >
    <v-card>
      <v-card-text>
        <div class="font-weight-bold mb-6">
          Название проекта
        </div>
        <v-text-field ref="textInput" v-model="name" class="mt-1" @keydown.enter="save()" />
      </v-card-text>
      <v-card-actions class="pb-4">
        <v-spacer />
        <v-btn
          color="primary" variant="flat"
          :width="160"
          :loading="saving"
          @click="save()"
        >
          Сохранить
        </v-btn>
        <v-spacer />
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
