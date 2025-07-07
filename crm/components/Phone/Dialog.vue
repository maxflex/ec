<script setup lang="ts">
const item = ref<PhoneResource>()
const { dialog, width } = useDialog('default')

function open(p: PhoneResource) {
  item.value = p
  dialog.value = true
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <PhoneDialogContent v-if="item" :item="item" />
  </v-dialog>
</template>
