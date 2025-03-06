<script setup lang="ts">
const { items } = defineProps<{
  items: SelectItems
}>()
const model = defineModel<string | number | boolean>()
const selectedItem = computed(() => items.find(e => e.value === model.value))

function toggle() {
  const index = items.findIndex(e => e.value === model.value)
  const nextIndex = index + 1
  model.value = nextIndex >= items.length ? items[0].value : items[nextIndex].value
}
</script>

<template>
  <a class="ui-toggler" @click="toggle()">
    {{ selectedItem?.title }}
  </a>
</template>

<style lang="scss">
.ui-toggler {
  cursor: pointer;
  user-select: none;
}
</style>
