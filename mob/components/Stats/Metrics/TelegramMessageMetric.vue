<script lang="ts" setup>
interface Filters {
  status?: number
  template: TelegramTemplate[]
  entity_type: EntityType[]
}

const filters = ref<Filters>({
  template: [],
  entity_type: [],
})

defineExpose({ filters })
</script>

<script lang="ts">
export default {
  label: 'Сообщения',
  filters: {
    template: [],
    entity_type: [],
  },
}
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
      label="Шаблон"
      :items="selectItems(TelegramTemplateLabel)"
    />
  </div>
  <div>
    <UiMultipleSelect
      v-model="filters.entity_type"
      label="Получатели"
      :items="selectItems(EntityTypeLabel)"
    />
  </div>
</template>
