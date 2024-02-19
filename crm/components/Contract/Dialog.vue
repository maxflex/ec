<script setup lang="ts">
import type { CompanyType, Contract, ContractVersion } from "~/utils/models"
import { YEARS, COMPANY_TYPE } from "~/utils/sment"

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
      <v-select
        label="Компания"
        :items="
          Object.keys(COMPANY_TYPE).map((value) => ({
            value,
            title: COMPANY_TYPE[value as CompanyType],
          }))
        "
        v-model="contract.company"
      />
      <UiDateInput v-model="version.date" />
    </div>
  </v-dialog>
</template>
