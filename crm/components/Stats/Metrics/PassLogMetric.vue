<script lang="ts" setup>
type LocalEntityType = typeof entityTypes[number]

interface Filters {
  entity_type?: LocalEntityType
}

const { client, clientParent, teacher, user } = EntityTypeValue
const entityTypes = [client, clientParent, teacher, user] as const
const selectItemsFiltered = Object.keys(EntityTypeLabel)
  .filter(key => entityTypes.includes(key as LocalEntityType))
  .map(value => ({
    value,
    title: EntityTypeLabel[value as EntityType],
  }))

const filters = ref<Filters>({})

defineExpose({ filters })
</script>

<script lang="ts">
export default {
  label: 'Постоянные пропуски',
  filters: {},
}
</script>

<template>
  <div>
    <UiClearableSelect v-model="filters.entity_type" :items="selectItemsFiltered" label="Кто проходил" />
  </div>
</template>
