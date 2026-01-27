<script setup lang="ts">
import type { TeacherResource } from '.'
import { cloneDeep } from 'lodash-es'
import { modelDefaults } from '.'

const emit = defineEmits<{
  (e: 'created' | 'updated', c: TeacherResource): void
}>()

const { dialog, width } = useDialog('medium')
const router = useRouter()
const teacher = ref<TeacherResource>(modelDefaults)
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const itemId = ref<number>()

function open(c: TeacherResource) {
  teacher.value = cloneDeep(c)
  dialog.value = true
}

function create() {
  itemId.value = undefined
  open(modelDefaults)
}

async function edit(teacherId: number) {
  itemId.value = teacherId
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<TeacherResource>(`teachers/${teacherId}`)
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
      body: cloneDeep(teacher.value),
    })
    if (data.value) {
      emit('updated', data.value)
    }
  }
  else {
    const { data } = await useHttp<TeacherResource>('teachers', {
      method: 'post',
      body: cloneDeep(teacher.value),
    })
    if (data.value) {
      await router.push({
        name: 'teachers-id',
        params: {
          id: data.value.id,
        },
      })
    }
  }

  // emit('saved')
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить преподавателя?')) {
    return
  }
  deleting.value = true
  const { status } = await useHttp(`teachers/${teacher.value.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    deleting.value = false
  }
  else {
    // emit('deleted', item.value)
    dialog.value = false
    setTimeout(() => (deleting.value = false), 300)
    await router.push({ name: 'teachers' })
  }
}

defineExpose({ create, edit })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div v-if="teacher" class="dialog-wrapper">
      <div class="dialog-header">
        <div v-if="teacher.id > 0">
          Редактирование преподавателя
          <div class="dialog-subheader">
            {{ teacher.user ? formatName(teacher.user) : 'неизвестно' }}
            <template v-if="teacher.created_at">
              {{ formatDateTime(teacher.created_at) }}
            </template>
          </div>
        </div>
        <span v-else> Добавить преподавателя </span>
        <div>
          <v-btn
            v-if="teacher.id > 0"
            icon="$delete"
            :size="48"
            variant="text"
            :loading="deleting"
            class="remove-btn"
            @click="destroy()"
          />
          <v-btn
            icon="$save"
            :size="48"
            variant="text"
            :loading="saving"
            @click="save()"
          />
        </div>
      </div>
      <div class="dialog-body">
        <div style="margin-bottom: 40px;">
          <AvatarLoader :key="teacher.id" :item="teacher" />
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
        <PhoneEditor v-model="teacher.phones" edit-telegram />

        <div class="double-input">
          <v-text-field
            v-model="teacher.passport.series"
            label="Серия паспорта"
          />
          <v-text-field
            v-model="teacher.passport.number"
            label="Номер паспорта"
          />
          <v-text-field
            v-model="teacher.passport.code"
            label="Код подразделения"
          />
        </div>
        <v-textarea
          v-model="teacher.passport.issued_by"
          label="Паспорт выдан"
          no-resize
          rows="3"
        />
        <v-textarea
          v-model="teacher.passport.address"
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
        <div>
          <v-checkbox
            v-model="teacher.is_published"
            label="Опубликован"
          />
          <v-checkbox
            v-model="teacher.is_split_balance"
            label="Разделять баланс"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
