<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  updated: [e: ClientPaymentResource]
  deleted: [e: ClientPaymentResource]
}>()
const { width, dialog } = useDialog('default')
const deleting = ref(false)
const saving = ref(false)
const loading = ref(false)
const itemId = ref<number>()

const printOptions: PrintOption[] = [
  { id: 9, label: 'платежка (наличные)' },
]

const modelDefaults: ClientPaymentResource = {
  id: newId(),
  sum: 0,
  date: today(),
  year: currentAcademicYear(),
  method: 'card',
  company: 'ooo',
  purpose: null,
  is_confirmed: false,
  is_return: false,
  client_id: newId(),
  pko_number: null,
  card_number: null,
}
const item = ref<ClientPaymentResource>(modelDefaults)

function create(clientId: number, year: Year) {
  itemId.value = undefined
  item.value = clone(modelDefaults)
  item.value.year = year
  item.value.client_id = clientId
  dialog.value = true
}

async function edit(e: ClientPaymentResource) {
  const { id } = e
  itemId.value = id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<ClientPaymentResource>(`client-payments/${id}`)
  if (data.value) {
    item.value = data.value
  }
  loading.value = false
}

async function save() {
  saving.value = true
  const method = itemId.value ? `put` : `post`
  const url = itemId.value ? `client-payments/${itemId.value}` : `client-payments`
  const { data } = await useHttp<ClientPaymentResource>(url, {
    method,
    body: item.value,
  })
  if (data.value) {
    emit('updated', data.value)
  }
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить платеж?')) {
    return
  }
  deleting.value = true
  await useHttp(`client-payments/${item.value.id}`, {
    method: 'delete',
  })
  emit('deleted', item.value)
  dialog.value = false
  setTimeout(() => deleting.value = false, 300)
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
          <template
            v-if="itemId"
          >
            <v-btn
              icon="$delete"
              :size="48"
              variant="text"
              :loading="deleting"
              class="remove-btn"
              @click="destroy()"
            />
            <v-menu>
              <template #activator="{ props }">
                <v-btn
                  v-bind="props"
                  icon="$print"
                  :size="48"
                  variant="text"
                />
              </template>
              <v-list>
                <v-list-item
                  v-for="p in printOptions" :key="p.id"
                  @click="print(p, { client_payment_id: item.id })"
                >
                  {{ p.label }}
                </v-list-item>
              </v-list>
            </v-menu>
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
          <v-select v-model="item.year" label="Учебный год" :items="selectItems(YearLabel)" />
        </div>
        <div>
          <v-select
            v-model="item.company"
            label="Компания"
            :items="selectItems(CompanyLabel)"
          />
        </div>
        <div>
          <v-select
            v-model="item.method"
            label="Способ оплаты"
            :items="selectItems(ClientPaymentMethodLabel)"
          />
        </div>
        <div v-if="item.method === 'card'">
          <v-text-field
            v-model="item.card_number"
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
          <v-text-field v-else disabled value="Будет присвоен" label="Номер ПКО" />
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
