<script setup lang="ts">
import type { Contract, ContractVersion } from "~/utils/models"
import { YEARS } from "~/utils/sment"

const dialog = ref(false)
const contract = ref<Contract>()
const version = ref<ContractVersion>()

function storeOrUpdate() {
  console.log("smenty")
}

function open(c: Contract, v: ContractVersion) {
  contract.value = { ...c }
  version.value = { ...v }
  dialog.value = true
}

defineExpose({ open })
</script>

<template>
  <v-dialog
    fullscreen
    v-model="dialog"
    transition="dialog-right-transition"
    content-class="dialog-right"
    :width="600"
  >
    <div
      class="dialog-content"
      v-if="contract && version"
      @submit.prevent="storeOrUpdate()"
    >
      <v-select
        label="Учебный год"
        :items="
          YEARS.map((value) => ({
            value,
            title: formatYear(value),
          }))
        "
        v-model="contract.year"
      />
    </div>
  </v-dialog>
</template>
