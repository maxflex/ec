<script setup lang="ts">
const { notSet = 'не установлено', nullify, expand } = defineProps<{
  expand: boolean
  notSet?: string
  nullify?: boolean
}>()
const model = defineModel<any>({ required: true })
const setNull = nullify || model.value === null
const input = ref()
function clear() {
  model.value = setNull ? null : undefined
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
      maxHeight: expand ? 'auto' : 300,
    }"
  >
    <template #prepend-item>
      <v-list-item
        base-color="gray"
        @click="clear()"
      >
        <v-list-item-title class="gray">
          {{ notSet }}
        </v-list-item-title>
        <template #prepend>
          <v-spacer />
        </template>
      </v-list-item>
    </template>
    <template v-if="'item' in $slots" #item="{ props, item }">
      <slot name="item" :props="props" :item="item" />
    </template>
    <template #no-data />
  </v-select>
</template>
