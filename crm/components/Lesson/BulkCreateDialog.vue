<script setup lang="ts">
import { cloneDeep } from 'lodash-es'
import { quarterEditablePrograms } from '.'

interface BulkItem {
  id: number
  weekday: Weekday | null
  cabinet: string | null
  time: string | null
}

interface Item {
  start_date: string | null
  end_date: string | null
  price?: string
  quarter?: Quarter
  year: Year
  group_id: number
  teacher_id?: number
  items: BulkItem[]
}
const emit = defineEmits<{
  updated: [l: LessonListResource[]]
}>()
const timeMask = { mask: '##:##' }
const modelDefaults: Item = {
  start_date: null,
  end_date: null,
  price: undefined,
  quarter: undefined,
  year: currentAcademicYear(),
  group_id: 0,
  teacher_id: undefined,
  items: [],
}

const weekdays: Array<{ value: Weekday, title: string }> = [
  { value: 0, title: 'ПН' },
  { value: 1, title: 'ВТ' },
  { value: 2, title: 'СР' },
  { value: 3, title: 'ЧТ' },
  { value: 4, title: 'ПТ' },
  { value: 5, title: 'СБ' },
  { value: 6, title: 'ВС' },
]

const { dialog, width } = useDialog('default')
const item = ref<Item>(modelDefaults)
const saving = ref(false)
const loading = ref(false)
const isQuarterEditable = ref(false)

function create(groupId: number, year: Year, p: Program) {
  item.value = {
    ...cloneDeep(modelDefaults),
    year,
    group_id: groupId,
  }
  dialog.value = true
  isQuarterEditable.value = quarterEditablePrograms.includes(p)
}

async function save() {
  saving.value = true
  const { data } = await useHttp<LessonListResource[]>(
    `lessons/bulk`,
    {
      method: 'post',
      body: cloneDeep(item.value),
    },
  )
  if (data.value) {
    emit('updated', data.value)
  }
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
}

function addItem() {
  item.value.items.push({
    id: newId(),
    weekday: null,
    cabinet: null,
    time: null,
  })
}

function removeItem(i: BulkItem) {
  const index = item.value.items.findIndex(e => e.id === i.id)
  item.value.items.splice(index, 1)
}

defineExpose({ create })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div class="dialog-wrapper">
      <div class="dialog-header">
        Добавить несколько занятий
        <v-btn
          icon="$save"
          :size="48"
          variant="text"
          :loading="saving"
          @click="save()"
        />
      </div>
      <UiLoader v-if="loading" />
      <div
        v-else
        class="dialog-body"
      >
        <div class="double-input">
          <UiDateInput v-model="item.start_date" :year="item.year" label="Дата от" />
          <UiDateInput v-model="item.end_date" :year="item.year" label="до" />
        </div>
        <div class="double-input">
          <v-text-field
            v-model="item.price"
            label="Цена"
            type="number"
            hide-spin-buttons
          />
          <UiClearableSelect
            v-if="isQuarterEditable"
            v-model="item.quarter"
            :items="selectItems(QuarterLabel, ['q1', 'q2', 'q3', 'q4'])"
            label="Четверть"
          />
        </div>
        <div>
          <TeacherSelector
            v-model="item.teacher_id"
            label="Преподаватель"
          />
        </div>
        <div v-for="i in item.items" :key="i.id" class="double-input">
          <div>
            <UiClearableSelect
              v-model="i.weekday"
              expand
              nullify
              :items="weekdays"
              placeholder="День"
            />
            <div class="input-actions">
              <span class="phone-editor__remove" @click="removeItem(i)">
                удалить
              </span>
            </div>
          </div>
          <div>
            <CabinetSelector
              v-model="i.cabinet"
              class="cabinett-selector"
              placeholder="Кабинет"
              :date="item.start_date"
              :date-end="item.end_date"
              :group-id="item.group_id"
            />
          </div>
          <div>
            <v-text-field
              v-model="i.time"
              v-maska="timeMask"
              placeholder="Время"
            />
          </div>
        </div>
        <div>
          <v-btn color="primary" block @click="addItem()">
            добавить
          </v-btn>
        </div>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.cabinett-selector {
  .v-select__selection {
    white-space: nowrap;
    overflow: hidden;
  }
}
</style>
