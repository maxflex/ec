<script setup lang="ts">
const { dialog, width } = useDialog('large')
const contractId = ref<number>()

function open(id: number) {
  contractId.value = id
  dialog.value = true
}

defineExpose({ open })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div
      v-if="contractId"
      class="dialog-wrapper"
    >
      <div class="dialog-header">
        Баланс по договору №{{ contractId }}
      </div>
      <div
        class="dialog-body"
      >
        <BalanceList
          :id="contractId"
          :entity="'contract'"
        />
      </div>
    </div>
  </v-dialog>
</template>
