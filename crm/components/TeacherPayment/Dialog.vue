<script setup lang="ts">
import { clone } from 'rambda'

const modelDefaults: TeacherPaymentResource = {
  id: -1,
  method: 'card',
  purpose: null,
  is_verified: false,
}

const { dialog, width } = useDialog('default')
const item = ref<TeacherPaymentResource>(modelDefaults)
const loading = ref(false)
const itemId = ref<number>()

function open(c: TeacherPaymentResource) {
  item.value = clone(c)
  dialog.value = true
}

function create() {
  itemId.value = undefined
  open(modelDefaults)
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
      emit('created', data.value)
    }
  }

  // emit('saved')
}

defineExpose({ create, edit })
const emit = defineEmits<{
  (e: 'created' | 'updated', c: TeacherPaymentResource): void
}>()
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
          @click="save()"
        />
      </div>
      <div class="dialog-body">
        <div>
          <v-select
            v-model="item.method"
            label="Метод"
            :items="selectItems(TeacherPaymentMethodLabel)"
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
          <UiDateInput v-model="item.date" />
        </div>
        <div>
          <v-text-field
            v-model="item.sum"
            type="number"
            hide-spin-buttons
            label="Сумма"
            suffix="руб."
          />
        </div>
        <div>
          <v-textarea
            v-model="item.purpose"
            label="Назначение"
            no-resize
          />
        </div>
        <div>
          <v-checkbox
            v-model="item.is_verified"
            label="Подтверждён"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
