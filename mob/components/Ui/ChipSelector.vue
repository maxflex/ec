<script setup lang="ts">
const { items, clearable, label = 'выберите' } = defineProps<{
  label: string
  clearable?: boolean
  items: Array<{
    title: string
    value: any
  }>
}>()

const model = defineModel()
const selectRef = ref<HTMLSelectElement | null>(null)

function openSelector() {
  selectRef.value?.click()
}
</script>

<template>
  <v-chip class="chip-selector" label @click="openSelector">
    {{ items.find(item => item.value === model)?.title || label }}
    <select ref="selectRef" v-model="model" class="hidden">
      <template v-if="clearable">
        <option value="undefined">
          не установлено
        </option>
        <hr>
      </template>
      <option v-for="item in items" :key="item.value" :value="item.value">
        {{ item.title }}
      </option>
    </select>
  </v-chip>
</template>

<style lang="scss">
.chip-selector {
  select {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
  }
}
</style>
