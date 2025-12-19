<script setup lang="ts">
import type { PrintDialog } from '#build/components'
import type { PrintOption } from '.'

const { dialog, width, transition } = useDialog('default')
const printDialog = ref<InstanceType<typeof PrintDialog>>()

interface Params {
  contract_id: number
  sum: string
}

const validated = ref(false)

const printOption: PrintOption = {
  id: 11,
  label: 'Счёт на оплату',
}

const params = ref<Params>({
  contract_id: -1,
  sum: '',
})

const input = ref()

function open(contractId: number) {
  params.value = {
    contract_id: contractId,
    sum: '',
  }
  dialog.value = true
  nextTick(() => input.value?.focus())
}

const required = [
  (value: any) => {
    if (value) {
      return true
    }

    return 'Поле обязательно для заполнения'
  },
]

function onSubmit() {
  setTimeout(() => {
    if (validated.value) {
      printDialog.value?.open(printOption, params.value, true)
    }
  }, 50)
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width" :transition="transition">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <div>
          {{ printOption.label }}
          <div class="dialog-subheader">
            К договору №{{ params.contract_id }}
          </div>
        </div>
        <v-btn
          :size="48"
          icon="$print"
          variant="text"
          type="submit"
          form="form"
        />
      </div>
      <v-form id="form" v-model="validated" validate-on="submit" class="dialog-body" @submit.prevent="onSubmit()">
        <div>
          <v-text-field
            ref="input"
            v-model="params.sum"
            type="number"
            hide-spin-buttons
            label="Сумма"
            :rules="required"
          />
        </div>
      </v-form>
    </div>
  </v-dialog>
  <LazyPrintDialog ref="printDialog" />
</template>
