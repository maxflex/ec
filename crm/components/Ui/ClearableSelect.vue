<script setup lang="ts">
const { items, expand } = defineProps<{
  label: string
  density?: 'comfortable'
  expand?: boolean
  items: {
    value: string | number
    title: string
  }[]
}>()
const model = defineModel<string | number >()
const input = ref()

function clear() {
  model.value = undefined
  nextTick(() => input.value.blur())
}
</script>

<template>
  <v-select
    v-bind="$attrs"
    ref="input"
    v-model="model"
    :label="label"
    :items="items"
    :density="density"
    :menu-props="{
      closeOnContentClick: true,
      minHeight: expand ? 'auto' : 350,
      maxHeight: expand ? 'auto' : 350,
    }"
  >
    <template #prepend-item>
      <v-list-item
        base-color="gray"
        @click="clear()"
      >
        <v-list-item-title class="gray">
          не установлено
        </v-list-item-title>
        <template #prepend>
          <v-spacer />
        </template>
      </v-list-item>
    </template>
  </v-select>
</template>
