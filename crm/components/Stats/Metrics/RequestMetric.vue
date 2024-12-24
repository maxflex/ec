<script lang="ts" setup>
interface Filters {
  direction?: Direction
  responsible_user_id?: number
  is_from_internet?: number
}

const filters = ref<Filters>({})

defineExpose({ filters })
</script>

<script lang="ts">
export default {
  label: 'Заявки',
  filters: {},
}
</script>

<template>
  <div>
    <UiClearableSelect v-model="filters.direction" :items="selectItems(DirectionLabel)" label="Направление" />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.is_from_internet"
      :items="yesNo('заявка с сайта', 'создана вручную')"
      label="Кто создал"
    />
  </div>
  <div>
    <UserSelector v-model="filters.responsible_user_id" label="Ответственный" />
  </div>
</template>
