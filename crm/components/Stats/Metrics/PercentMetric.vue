<script lang="ts">
const RoundFilterLabel = {
  '3': '+3',
  '2': '+2',
  '1': '+1',
  '0': 'не округлять',
  '-1': '-1',
  '-2': '-2',
  '-3': '-3',
} as const

type RoundFilter = keyof typeof RoundFilterLabel

interface Filters {
  round: RoundFilter
}

const filterDefaults: Filters = {
  // @ts-ignore
  round: 0,
}

export default {
  label: 'Доля',
  filters: { ...filterDefaults },
  special: true,
}
</script>

<script lang="ts" setup>
const filters = ref<Filters>({ ...filterDefaults })
defineExpose({ filters })
</script>

<template>
  <div>
    <UiClearableSelect
      v-model="filters.round"
      label="Округление"
      :items="selectItems(RoundFilterLabel)"
    />
  </div>
</template>
