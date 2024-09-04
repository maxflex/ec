<script lang="ts" setup>
export interface Filters {
  template?: TelegramTemplate
  type?: number
}

const emit = defineEmits<{
  apply: [f: Filters]
}>()

const filters = ref<Filters>({})

watch(filters.value, () => emit('apply', filters.value))
</script>

<template>
  <div>
    <UiClearableSelect
      v-model="filters.template"
      label="Шаблон"
      :items="selectItems(TelegramTemplateLabel)"
      density="comfortable"
    />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.type"
      label="Тип"
      :items="yesNo('групповые', 'личные')"
      density="comfortable"
    />
  </div>
</template>
