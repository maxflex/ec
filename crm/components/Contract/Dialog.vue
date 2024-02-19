<script setup lang="ts">
import type { ContractVersion } from "~/utils/models"

const dialog = ref(false)
const version = ref<ContractVersion>()

function storeOrUpdate() {
  console.log("smenty")
}

function open(cv: ContractVersion) {
  console.log("OPENED", version)
  dialog.value = true
  version.value = cv
}

defineExpose({ open })
</script>

<template>
  <v-dialog
    fullscreen
    v-model="dialog"
    transition="dialog-right-transition"
    content-class="dialog-right"
  >
    <v-form @submit.prevent="storeOrUpdate()">
      <pre v-if="version">
        {{ version }}
      </pre>
    </v-form>
  </v-dialog>
</template>
