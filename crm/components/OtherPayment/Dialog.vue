<script setup lang="ts">
import type { AllPaymentsResource, OtherPaymentResource } from '.'
import { cloneDeep } from 'lodash-es'
import { apiUrl, modelDefaults, OtherPaymentMethodLabel } from '.'

const emit = defineEmits<{
  updated: [e: AllPaymentsResource]
  deleted: [id: number]
}>()

const { width, dialog } = useDialog('default')
const saving = ref(false)
const loading = ref(false)
const itemId = ref<number>()
const item = ref<OtherPaymentResource>(cloneDeep(modelDefaults))

function create() {
  itemId.value = undefined
  item.value = cloneDeep(modelDefaults)
  dialog.value = true
}

async function edit(id: number) {
  itemId.value = id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<OtherPaymentResource>(`${apiUrl}/${id}`)
  item.value = data.value!
  loading.value = false
}

async function save() {
  saving.value = true
  const method = itemId.value ? `put` : `post`
  const url = itemId.value ? `${apiUrl}/${itemId.value}` : apiUrl
  const { data, error } = await useHttp<AllPaymentsResource>(url, {
    method,
    body: cloneDeep(item.value),
  })
  if (error.value) {
    useGlobalMessage(`Заполните все поля`, 'error')
    saving.value = false

    return
  }
  emit('updated', data.value!)
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
}

async function destroy() {
  useGlobalMessage('Платеж удален', 'success')
  emit('deleted', itemId.value!)
  dialog.value = false
}

defineExpose({ create, edit })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <span v-if="itemId">
          Редактировать платеж
          <div class="dialog-subheader">
            <template v-if="item.user && item.created_at">
              {{ formatName(item.user) }}
              {{ formatDateTime(item.created_at) }}
            </template>
          </div>
        </span>
        <span v-else>
          Добавить платеж
        </span>
        <div>
          <template v-if="itemId">
            <CrudDeleteBtn
              :id="itemId"
              :api-url="apiUrl"
              confirm-text="Вы уверены, что хотите удалить платеж?"
              @deleted="destroy()"
            />
            <PrintBtn :items="[9, 14]" :extra="{ other_payment_id: itemId }" />
          </template>
          <v-btn
            icon="$save"
            :size="48"
            variant="text"
            :loading="saving"
            @click="save()"
          />
        </div>
      </div>
      <UiLoader v-if="loading" />
      <div v-else class="dialog-body">
        <div>
          <v-text-field
            v-model="item.sum"
            label="Сумма"
            type="number"
            hide-spin-buttons
          />
        </div>
        <UiDateInput v-model="item.date" today-btn />
        <div>
          <v-select
            v-model="item.method"
            label="Способ оплаты"
            :items="selectItems(OtherPaymentMethodLabel, item.method === 'sbp' ? undefined : ['card', 'cash'])"
          />
        </div>
        <div v-if="item.method === 'card'">
          <v-text-field
            v-model="item.card_number"
            v-maska="{ mask: '#####' }"
            placeholder="∗∗∗∗ ∗∗∗∗ ∗∗∗∗ ∗∗∗∗"
            label="Номер карты"
          />
        </div>
        <div v-else-if="item.method === 'cash'">
          <v-text-field
            v-if="itemId"
            v-model="item.pko_number"
            label="Номер ПКО"
            type="number"
            hide-spin-buttons
          />
          <v-text-field v-else disabled label="Будет присвоен номер ПКО" />
        </div>

        <div>
          <v-text-field
            v-model="item.last_name"
            label="Фамилия"
          />
        </div>
        <div>
          <v-text-field
            v-model="item.first_name"
            label="Имя"
          />
        </div>
        <div>
          <v-text-field
            v-model="item.middle_name"
            label="Отчество"
          />
        </div>
        <div>
          <v-textarea
            v-model="item.purpose"
            label="Назначение"
            no-resize
            rows="3"
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
      </div>
    </div>
  </v-dialog>
</template>
