<script lang="ts">
interface Filters {
  status?: number
  template: TelegramTemplate[]
  entity_type: EntityType[]
}

const filterDefaults: Filters = {
  template: [],
  entity_type: [],
}

const receivers = selectItems({
  'App\\Models\\Client': 'Ученик',
  'App\\Models\\ClientParent': 'Представитель',
  'App\\Models\\Teacher': 'Преподаватель',
})

export default {
  label: 'Сообщения',
  filters: { ...filterDefaults },
}
</script>

<script lang="ts" setup>
const filters = ref<Filters>({ ...filterDefaults })
defineExpose({ filters })
</script>

<template>
  <div>
    <UiClearableSelect
      v-model="filters.status"
      label="Статус доставки"
      :items="yesNo('доставлено', 'не доставлено')"
    />
  </div>
  <div>
    <UiMultipleSelect
      v-model="filters.template"
      label="Шаблон отправки"
      :items="selectItems(TelegramTemplateLabel)"
    />
  </div>
  <div>
    <UiMultipleSelect
      v-model="filters.entity_type"
      label="Получатель"
      :items="receivers"
    />
  </div>
</template>
