<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{ (e: 'updated' | 'deleted', c: TeacherServiceResource): void }>()

const modelDefaults: TeacherServiceResource = {
  id: newId(),
  year: currentAcademicYear(),
  date: today(),
  purpose: null,
  sum: 0,
}

const { dialog, width } = useDialog('default')
const item = ref<TeacherServiceResource>(modelDefaults)
const loading = ref(false)
const itemId = ref<number>()
const sumInput = ref()
const deleting = ref(false)
function open(c: TeacherServiceResource) {
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
}
async function edit(c: TeacherServiceResource) {
  itemId.value = c.id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<TeacherServiceResource>(`teacher-services/${c.id}`)
  if (data.value) {
    open(data.value)
  }
  loading.value = false
}

async function save() {
  dialog.value = false
  if (itemId.value) {
    const { data } = await useHttp<TeacherServiceResource>(`teacher-services/${itemId.value}`, {
      method: 'put',
      body: item.value,
    })
    if (data.value) {
      emit('updated', data.value)
    }
  }
  else {
    const { data } = await useHttp<TeacherServiceResource>('teacher-services', {
      method: 'post',
      body: item.value,
    })
    if (data.value) {
      emit('updated', data.value)
    }
  }
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить допуслугу?')) {
    return
  }
  deleting.value = true
  const { status } = await useHttp(`teacher-services/${item.value.id}`, {
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
          Редактирование допуслуги
          <div class="dialog-subheader">
            <template v-if="item.user && item.created_at">
              {{ formatName(item.user) }}
              {{ formatDateTime(item.created_at) }}
            </template>
          </div>
        </div>
        <span v-else> Добавить допуслугу </span>
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
          <v-textarea
            v-model="item.purpose"
            label="Назначение"
            no-resize
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
