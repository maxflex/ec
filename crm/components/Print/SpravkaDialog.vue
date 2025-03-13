<script setup lang="ts">
import type { PrintDialog } from '#build/components'

const { dialog, width, transition } = useDialog('default')
const printDialog = ref<InstanceType<typeof PrintDialog>>()

interface SpravkaParams {
  client_id: number
  year: Year
  seq?: string
  date: string
  parent_inn?: string
  parent_birthday?: string
  client_passport_issued_at?: string
  client_inn?: string
  sum?: string
  company: Company
}

const printOption: PrintOption = {
  id: 15,
  label: 'Справка об оплате образовательных услуг',
}

const params = ref<SpravkaParams>({
  client_id: -1,
  date: today(),
  year: currentAcademicYear(),
  company: 'ooo',
})

function open(clientId: number) {
  params.value.client_id = clientId
  dialog.value = true
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width" :transition="transition">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        {{ printOption.label }}
        <v-btn
          :size="48"
          icon="$print"
          variant="text"
          @click="printDialog?.open(printOption, params, true)"
        />
      </div>
      <div class="dialog-body">
        <div>
          <v-select v-model="params.company" :items="selectItems(CompanyLabel)" label="Компания" />
        </div>
        <div>
          <UiYearSelector v-model="params.year" label="Отчетный год" />
        </div>
        <div>
          <UiDateInput
            v-model="params.date"
            label="Дата"
            manual
          />
        </div>
        <div>
          <v-text-field
            v-model="params.seq"
            type="number"
            hide-spin-buttons
            label="Номер справки"
          />
        </div>
        <div>
          <v-text-field
            v-model="params.sum"
            type="number"
            hide-spin-buttons
            label="Сумма"
          />
        </div>
        <div>
          <v-text-field
            v-model="params.parent_inn"
            type="number"
            hide-spin-buttons
            label="ИНН представителя"
          />
        </div>
        <div>
          <v-text-field
            v-model="params.client_inn"
            type="number"
            hide-spin-buttons
            label="ИНН ученика"
          />
        </div>

        <div>
          <UiDateInput
            v-model="params.parent_birthday"
            label="Дата рождения представителя"
            manual
          />
        </div>
        <div>
          <UiDateInput
            v-model="params.client_passport_issued_at"
            label="Дата выдачи паспорта ученика"
            manual
          />
        </div>
      </div>
    </div>
  </v-dialog>
  <LazyPrintDialog ref="printDialog" />
</template>
