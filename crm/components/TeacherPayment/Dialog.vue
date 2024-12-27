<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  updated: [item: TeacherPaymentResource]
  deleted: [item: TeacherPaymentResource]
}>()

const modelDefaults: TeacherPaymentResource = {
  id: newId(),
  year: currentAcademicYear(),
  date: today(),
  method: 'card',
  card_number: null,
  sum: 0,
  is_confirmed: false,
}

const { dialog, width } = useDialog('default')
const item = ref<TeacherPaymentResource>(modelDefaults)
const loading = ref(false)
const itemId = ref<number>()
const sumInput = ref()
const deleting = ref(false)
function open(c: TeacherPaymentResource) {
  item.value = clone(c)
  dialog.value = true
}

function create(teacherId: number, year: Year) {
  itemId.value = undefined
  open({
    ...modelDefaults,
    year,
    teacher_id: teacherId,
  })
  nextTick(() => {
    sumInput.value.reset()
    sumInput.value.focus()
  })
}
async function edit(c: TeacherPaymentResource) {
  itemId.value = c.id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<TeacherPaymentResource>(`teacher-payments/${c.id}`)
  if (data.value) {
    open(data.value)
  }
  loading.value = false
}

async function save() {
  dialog.value = false
  if (itemId.value) {
    const { data } = await useHttp<TeacherPaymentResource>(`teacher-payments/${itemId.value}`, {
      method: 'put',
      body: item.value,
    })
    if (data.value) {
      emit('updated', data.value)
    }
  }
  else {
    const { data } = await useHttp<TeacherPaymentResource>('teacher-payments', {
      method: 'post',
      body: item.value,
    })
    if (data.value) {
      emit('updated', data.value)
    }
  }
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить платеж?')) {
    return
  }
  deleting.value = true
  const { status } = await useHttp(`teacher-payments/${item.value.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    deleting.value = false
  }
  else {
    emit('deleted', item.value)
    dialog.value = false
    setTimeout(() => deleting.value = false, 300)
  }
}

defineExpose({ create, edit })
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
        <div v-if="item.id > 0">
          Редактирование платежа
          <div class="dialog-subheader">
            <template v-if="item.user && item.created_at">
              {{ formatName(item.user) }}
              {{ formatDateTime(item.created_at) }}
            </template>
          </div>
        </div>
        <span v-else> Добавить платеж </span>
        <div>
          <v-btn
            v-if="item.id > 0"
            icon="$delete"
            :size="48"
            class="remove-btn"
            variant="text"
            :loading="deleting"
            @click="destroy()"
          />
          <v-btn
            icon="$save"
            :size="48"
            variant="text"
            @click="save()"
          />
        </div>
      </div>
      <div class="dialog-body">
        <div>
          <v-text-field
            ref="sumInput"
            v-model="item.sum"
            type="number"
            hide-spin-buttons
            label="Сумма"
            suffix="руб."
          />
        </div>
        <div>
          <v-select
            v-model="item.method"
            label="Метод"
            :items="selectItems(TeacherPaymentMethodLabel)"
          />
        </div>
        <div v-if="item.method === 'card'">
          <v-text-field
            v-model="item.card_number"
            label="Номер карты"
          />
        </div>
        <div>
          <UiClearableSelect
            v-model="item.year"
            label="Учебный год"
            :items="selectItems(YearLabel)"
          />
        </div>
        <div>
          <UiDateInput v-model="item.date" today-btn />
        </div>
        <div>
          <v-checkbox
            v-model="item.is_confirmed"
            label="Подтверждён"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
