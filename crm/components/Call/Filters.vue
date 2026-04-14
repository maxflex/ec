<script setup lang="ts">
import type { CallerType } from '~/components/Call'
import { CallerTypeLabel } from '~/components/Call'

export type CallStatusFilter = 'incoming' | 'outgoing' | 'missed' | 'missed_callback'
export type CallDurationFilter = 'no_conversation' | 'short' | 'medium' | 'long' | 'very_long'

export interface CallFilters {
  number?: string
  user_id?: number
  call_status: CallStatusFilter[]
  caller_type: CallerType[]
  call_duration: CallDurationFilter[]
}

const model = defineModel<CallFilters>({ required: true })
const numberInput = ref(model.value.number || '')

const callStatusLabel: Record<CallStatusFilter, string> = {
  incoming: 'входящий',
  outgoing: 'исходящий',
  missed: 'пропущенные',
  missed_callback: 'перезвонили',
}

const callDurationLabel: Record<CallDurationFilter, string> = {
  short: 'короткие (< 1 мин)',
  medium: 'средние (1–5 мин)',
  long: 'длинные (5–10 мин)',
  very_long: 'очень длинные (> 10 мин)',
  no_conversation: 'без разговора',
}

// Применяем номер по Enter, чтобы не дёргать API на каждую нажатую клавишу.
function applyNumberFilter() {
  model.value.number = numberInput.value || undefined
}

function clearNumberFilter() {
  numberInput.value = ''
  model.value.number = undefined
}

watch(() => model.value.number, (value) => {
  numberInput.value = value || ''
})
</script>

<template>
  <UserSelector
    v-model="model.user_id"
    density="comfortable"
    label="Пользователь"
  />
  <UiMultipleSelect
    v-model="model.caller_type"
    density="comfortable"
    label="Тип разговора"
    :items="selectItems(CallerTypeLabel)"
    expand
  />
  <UiMultipleSelect
    v-model="model.call_duration"
    expand
    density="comfortable"
    label="Время разговора"
    :items="selectItems(callDurationLabel)"
  />
  <UiMultipleSelect
    v-model="model.call_status"
    density="comfortable"
    label="Тип звонка"
    :items="selectItems(callStatusLabel)"
  />

  <div class="relative">
    <v-text-field
      v-model="numberInput"
      label="Номер телефона"
      density="comfortable"
      @keydown.enter="applyNumberFilter"
      @blur="applyNumberFilter"
    />
    <UiUnderInput v-if="numberInput" @click="clearNumberFilter" />
  </div>
</template>
