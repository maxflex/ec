<script lang="ts" setup>
// const paymentTypeLabel = {
//   payments: 'только платежи',
//   returns: 'только возвраты',
//   diff: 'разница',
// }

interface Filters {
  year?: Year
  program?: Program
  is_moderated?: number
  is_published?: number
  // type: 'payments' | 'returns'
}

const filters = ref<Filters>({})

defineExpose({ filters })
</script>

<script lang="ts">
export default {
  label: 'Отчёты',
}
</script>

<template>
  <div>
    <UiClearableSelect v-model="filters.year" :items="selectItems(YearLabel)" label="Учебный год" />
  </div>
  <div>
    <UiClearableSelect v-model="filters.program" :items="selectItems(ProgramLabel)" label="Программа" />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.is_moderated"
      :items="yesNo()"
      label="Промодерировано"
    />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.is_published"
      :items="yesNo()"
      label="Опубликовано"
    />
  </div>
</template>
