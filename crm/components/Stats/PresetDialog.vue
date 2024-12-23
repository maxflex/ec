<script setup lang="ts">
import type { StatsParams, StatsPreset } from './Metrics'
import { VDialogTransition } from 'vuetify/lib/components/transitions'

const emit = defineEmits<{
  save: [e: StatsPreset]
}>()
const dialog = ref(false)
const saving = ref(false)
const nameInput = ref()
const preset = ref<StatsPreset>({
  id: newId(),
  name: '',
  params: {
    mode: 'day',
    metrics: [],
  },
})

function open(params: StatsParams) {
  dialog.value = true
  preset.value = {
    id: newId(),
    name: '',
    params,
  }
  setTimeout(() => nameInput.value.focus(), 50)
}

async function save() {
  if (preset.value.params.metrics.length === 0) {
    return
  }
  if (!preset.value.name) {
    setTimeout(() => nameInput.value.focus(), 50)
    return
  }
  saving.value = true
  const body = { ...preset.value }
  // делаем ID положительными, чтобы в будущем загрузка пресета
  // не конфликтовала с добавлением новых метрик
  body.params.metrics = body.params.metrics.map(m => ({
    ...m,
    id: Math.abs(m.id),
  }))
  const { data } = await useHttp<StatsPreset>(
    `stats-presets`,
    {
      method: 'post',
      body,
    },
  )
  dialog.value = false
  saving.value = false
  emit('save', data.value!)
}

defineExpose({ open })
</script>

<template>
  <v-dialog
    v-model="dialog"
    max-width="500"
    :fullscreen="false"
    location="center"
    :content-class="null"
    :transition="{ component: VDialogTransition }"
  >
    <v-card>
      <v-card-text>
        <div class="font-weight-bold mb-6">
          Сохранение пресета
        </div>
        <v-text-field ref="nameInput" v-model="preset.name" class="mt-1" label="Название" @keydown.enter="save()" />
      </v-card-text>
      <v-card-actions class="pb-4">
        <v-spacer />
        <v-btn
          color="primary" variant="flat"
          :width="160"
          :disabled="preset.params.metrics.length === 0"
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
