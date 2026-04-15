<script lang="ts">
import type { CallDurationFilter, CallerType, CallType } from '~/components/Call'
import { CallDurationLabel, CallerTypeLabel } from '~/components/Call'

interface Filters {
  type?: CallType
  answered_at?: number
  caller_type: CallerType[]
  call_duration: CallDurationFilter[]
  user_id: number[]
}

const filterDefaults: Filters = {
  caller_type: [],
  call_duration: [],
  user_id: [],
}

export default {
  label: 'Звонки',
  filters: { ...filterDefaults },
}
</script>

<script lang="ts" setup>
// В шаблоне используем общие label из модуля звонков, без локальных дублей.
const callerTypeLabel = CallerTypeLabel
// В метрике "Звонки" не показываем вариант "без разговора".
const callDurationLabel = Object.fromEntries(
  Object.entries(CallDurationLabel).filter(([key]) => key !== 'no_conversation'),
) as Record<Exclude<CallDurationFilter, 'no_conversation'>, string>

const filters = ref<Filters>({ ...filterDefaults })
defineExpose({ filters })
</script>

<template>
  <div>
    <UserSelector v-model="filters.user_id" label="Пользователь" multiple />
  </div>
  <div>
    <UiMultipleSelect
      v-model="filters.caller_type"
      label="Тип разговора"
      :items="selectItems(callerTypeLabel)"
      expand
    />
  </div>
  <div>
    <UiMultipleSelect
      v-model="filters.call_duration"
      label="Время разговора"
      :items="selectItems(callDurationLabel)"
      expand
    />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.type"
      label="Тип звонка"
      :items="
        selectItems({
          incoming: 'входящий',
          outgoing: 'исходящий',
        })
      "
    />
  </div>
  <div>
    <UiClearableSelect v-model="filters.answered_at" label="Разговор состоялся" :items="yesNo()" />
  </div>
</template>
