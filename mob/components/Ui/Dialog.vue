<script setup lang="ts">
const model = defineModel<{
  dialog: globalThis.Ref<boolean>
  width: number
  transition: globalThis.Ref<boolean>
}>({
  default: () => ({
    dialog: false,
    width: 0,
    transition: '',
  }),
})
</script>

<template>
  <v-dialog
    v-model="model.dialog.value"
    :width="model.width"
  >
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <slot name="header" />
      </div>
    </div>
  </v-dialog>
</template>
