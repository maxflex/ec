<script setup lang="ts">
import type { TeacherPaymentResource } from '.'
import { clone } from 'lodash-es'
import { apiUrl, modelDefaults } from '.'

interface Suggestions {
  to_pay_lessons: number
  to_pay_other: number
}

const emit = defineEmits<{
  updated: [item: TeacherPaymentResource]
  deleted: [item: TeacherPaymentResource]
}>()

const suggestions = ref<Suggestions>()

const cardNumberMask = { mask: '#####' }

const { dialog, width } = useDialog('default')
const item = ref<TeacherPaymentResource>(modelDefaults)
const loading = ref(false)
const itemId = ref<number>()
const sumInput = ref()
function open(c: TeacherPaymentResource) {
  item.value = clone(c)
  dialog.value = true
}

function create(teacherId: number) {
  itemId.value = undefined
  open({
    ...modelDefaults,
    year: currentAcademicYear(),
    teacher_id: teacherId,
  })
  nextTick(() => {
    sumInput.value.reset()
    sumInput.value.focus()
  })
  loadPresetSuggestions()
}
async function edit(c: TeacherPaymentResource) {
  itemId.value = c.id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<TeacherPaymentResource>(`${apiUrl}/${c.id}`)
  if (data.value) {
    open(data.value)
  }
  loading.value = false
}

async function save() {
  dialog.value = false
  if (itemId.value) {
    const { data } = await useHttp<TeacherPaymentResource>(`${apiUrl}/${itemId.value}`, {
      method: 'put',
      body: item.value,
    })
    if (data.value) {
      emit('updated', data.value)
    }
  }
  else {
    const { data } = await useHttp<TeacherPaymentResource>(apiUrl, {
      method: 'post',
      body: item.value,
    })
    if (data.value) {
      emit('updated', data.value)
    }
  }
}

async function loadPresetSuggestions() {
  const { data } = await useHttp<Suggestions>(
    `${apiUrl}/get-suggestions/${item.value.teacher_id}`,
    {
      params: {
        year: item.value.year,
      },
    },
  )
  suggestions.value = data.value!
}

function onDeleted() {
  emit('deleted', item.value)
  dialog.value = false
}

defineExpose({ create, edit })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div v-if="item" class="dialog-wrapper">
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
          <DialogDeleteBtn
            :id="item.id"
            :api-url="apiUrl"
            confirm-text="Вы уверены, что хотите удалить платеж?"
            @deleted="onDeleted()"
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
          <a v-if="item.id < 0 && suggestions !== undefined" class="date-input__today d-flex ga-4">
            <span v-if="suggestions.to_pay_lessons" class="text-black">
              офф:
              <a @click="item.sum = suggestions.to_pay_lessons">
                {{ formatPrice(suggestions.to_pay_lessons) }}
              </a>
            </span>
            <span v-if="suggestions.to_pay_other" class="text-black">
              неофф:
              <a @click="item.sum = suggestions.to_pay_other">
                {{ formatPrice(suggestions.to_pay_other) }}
              </a>
            </span>
          </a>
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
            v-maska="cardNumberMask"
            hide-spin-buttons
            type="number"
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
