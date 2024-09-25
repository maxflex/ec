<script setup lang="ts">
const { expand } = defineProps<{ expand?: boolean }>()
const model = defineModel<any>({ required: true })
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
    :menu-props="{
      closeOnContentClick: true,
      minHeight: expand ? 'auto' : undefined,
      maxHeight: expand ? 'auto' : undefined,
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
