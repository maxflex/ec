<script setup lang="ts">
import { cloneDeep } from 'lodash'
import type { ClientPayment, Contract } from '~/utils/models'
import { CLIENT_PAYMENT_METHOD, COMPANY_TYPE } from '~/utils/sment'

const { dialog, width } = useDialog()
const item = ref<ClientPayment>()
const sumInput = ref()

function open(p: ClientPayment) {
  item.value = cloneDeep(p)
  dialog.value = true
}

function create(c: Contract) {
  item.value = {
    id: -1,
    sum: 0,
    date: today(),
    year: YEARS[0],
    method: 'card',
    entity_type: 'App\\Models\\Contract',
    entity_id: c.id,
    company: 'ip',
    purpose: null,
    extra: null,
    user_id: 1,
    is_confirmed: false,
    is_return: false,
    created_at: null,
    updated_at: null,
  }
  dialog.value = true
  nextTick(() => {
    sumInput.value.reset()
    sumInput.value.focus()
  })
}

defineExpose({ open, create })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div
      v-if="item"
      class="dialog-wrapper"
    >
      <div class="dialog-header">
        <span v-if="item.id > 0"> Редактирование платежа </span>
        <span v-else> Добавить платеж </span>
        <v-btn
          icon="$save"
          :size="48"
          variant="text"
          @click="dialog = false"
        />
      </div>
      <div class="dialog-body">
        <div>
          <v-text-field
            ref="sumInput"
            v-model="item.sum"
            label="Сумма"
            type="number"
            hide-spin-buttons
          />
        </div>
        <UiDateInput v-model="item.date" />
        <div>
          <UiYearSelector v-model="item.year" />
        </div>
        <div>
          <v-select
            v-model="item.method"
            label="Способ оплаты"
            :items="
              Object.keys(CLIENT_PAYMENT_METHOD).map((value) => ({
                value,
                title: CLIENT_PAYMENT_METHOD[value],
              }))
            "
          />
        </div>
        <div>
          <v-select
            v-model="item.company"
            label="Компания"
            :items="
              Object.keys(COMPANY_TYPE).map((value) => ({
                value,
                title: COMPANY_TYPE[value],
              }))
            "
          />
        </div>
        <div>
          <v-checkbox
            v-model="item.is_confirmed"
            label="Подтверждён"
          />
          <v-checkbox
            v-model="item.is_return"
            label="Возврат"
          />
        </div>
        <!-- <pre>
          {{ item }}
        </pre> -->
      </div>
    </div>
  </v-dialog>
</template>
