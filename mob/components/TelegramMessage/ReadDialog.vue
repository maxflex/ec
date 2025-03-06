<script setup lang="ts">
const { dialog, width } = useDialog('default')
const text = ref('')

function open(t: string) {
  text.value = t
  dialog.value = true
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-body">
        <div style="white-space: pre-line" v-html="text" />
      </div>
    </div>
  </v-dialog>
</template>
