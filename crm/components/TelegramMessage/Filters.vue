<script lang="ts" setup>
export interface TelegramMessageFilters {
  number?: string
  status?: number
  template?: TelegramTemplate
}

const model = defineModel<TelegramMessageFilters>({ required: true })
const numberInput = ref(model.value.number || '')

// Применяем номер только по Enter/blur, чтобы не стрелять запросом на каждую клавишу.
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
  <UiClearableSelect
    v-model="model.status"
    label="Статус доставки"
    :items="yesNo('доставлено', 'не доставлено')"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.template"
    label="Шаблон"
    :items="selectItems(TelegramTemplateLabel)"
    density="comfortable"
  />
</template>
