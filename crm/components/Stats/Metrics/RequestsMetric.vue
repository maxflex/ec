<script lang="ts" setup>
interface Filters {
  program?: Program
  responsible_user_id?: number
}

const filters = ref<Filters>({})

defineExpose({ filters })
</script>

<script lang="ts">
export default {
  label: 'Заявки',
}
</script>

<template>
  <div>
    <UiClearableSelect v-model="filters.program" :items="selectItems(ProgramLabel)" label="Программа" />
  </div>
  <div>
    <UserSelector v-model="filters.responsible_user_id" label="Ответственный" />
  </div>
</template>
