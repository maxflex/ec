<script lang="ts">
interface Filters {
  type?: CallType
  answered_at?: number
  user_id: number[]
}

const filterDefaults: Filters = {
  user_id: [],
}

export default {
  label: 'Звонки',
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
      v-model="filters.type"
      label="Тип"
      :items="selectItems({
        incoming: 'входящий',
        outgoing: 'исходящий',
      })"
    />
  </div>
  <div>
    <UserSelector v-model="filters.user_id" label="Пользователь" multiple />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.answered_at"
      label="Разговор состоялся"
      :items="yesNo()"
    />
  </div>
</template>
