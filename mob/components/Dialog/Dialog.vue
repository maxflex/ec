<script setup lang="ts">
// WIP
const { dialog, width } = useDialog('default')
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-header"></div>
  </v-dialog>
</template>
