<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  (e: 'created' | 'updated', c: TeacherResource): void
}>()

const modelDefaults: TeacherResource = {
  id: newId(),
  first_name: null,
  last_name: null,
  middle_name: null,
  photo_url: null,
  phones: [],
  subjects: [],
  status: 'active',
}

const { dialog, width } = useDialog('x-large')
const teacher = ref<TeacherResource>(modelDefaults)
const loading = ref(false)
const itemId = ref<number>()

function open(c: TeacherResource) {
  teacher.value = clone(c)
  dialog.value = true
}

function create() {
  itemId.value = undefined
  open(modelDefaults)
}
async function edit(c: TeacherResource) {
  itemId.value = c.id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<TeacherResource>(`teachers/${c.id}`)
  if (data.value) {
    open(data.value)
  }
  loading.value = false
}

async function save() {
  dialog.value = false
  if (itemId.value) {
    const { data } = await useHttp<TeacherResource>(`teachers/${itemId.value}`, {
      method: 'put',
      body: teacher.value,
    })
    if (data.value) {
      emit('updated', data.value)
    }
  }
  else {
    const { data } = await useHttp<TeacherResource>('teachers', {
      method: 'post',
      body: teacher.value,
    })
    if (data.value) {
      emit('created', data.value)
    }
  }

  // emit('saved')
}

defineExpose({ create, edit })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div
      v-if="teacher"
      class="dialog-wrapper"
    >
      <div class="dialog-header">
        <span v-if="teacher.id > 0"> Редактирование преподавателя </span>
        <span v-else> Добавить преподавателя </span>
        <v-btn
          icon="$save"
          :size="48"
          variant="text"
          @click="save()"
        />
      </div>
      <div class="dialog-body-2-col">
        <div class="dialog-body">
          <div style="margin-bottom: 40px;">
            <AvatarLoader
              :key="teacher.id"
              entity="teacher"
              :item="teacher"
            />
          </div>
          <div class="double-input">
            <v-text-field
              v-model="teacher.last_name"
              label="Фамилия"
            />
            <v-text-field
              v-model="teacher.first_name"
              label="Имя"
            />
          </div>
          <div class="double-input">
            <v-text-field
              v-model="teacher.middle_name"
              label="Отчество"
            />
            <v-text-field
              v-model="teacher.so"
              label="Рейтинг"
              type="number"
              hide-spin-buttons
            />
          </div>
          <div class="double-input">
            <v-select
              v-model="teacher.status"
              label="Статус"
              :items="selectItems(TeacherStatusLabel)"
            />
            <v-select
              v-model="teacher.subjects"
              label="Предметы"
              :items="selectItems(SubjectLabel)"
              multiple
            />
          </div>
          <PhoneEditor v-model="teacher.phones" />
        </div>
        <div class="dialog-body">
          <div class="double-input">
            <v-text-field
              v-model="teacher.passport_series"
              label="Серия паспорта"
            />
            <v-text-field
              v-model="teacher.passport_number"
              label="Номер паспорта"
            />
            <v-text-field
              v-model="teacher.passport_code"
              label="Код подразделения"
            />
          </div>
          <v-textarea
            v-model="teacher.passport_issued_by"
            label="Паспорт выдан"
            no-resize
            rows="3"
          />
          <v-textarea
            v-model="teacher.passport_address"
            label="Адрес регистрации"
            no-resize
            rows="3"
          />
          <v-textarea
            v-model="teacher.photo_desc"
            label="Короткое описание"
            no-resize
            rows="3"
          />
          <v-textarea
            v-model="teacher.desc"
            label="Описание"
            no-resize
            rows="9"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
