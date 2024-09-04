<script setup lang="ts">
import { clone } from 'rambda'
import { LessonFileTypeLabel } from '~/utils/labels'

const emit = defineEmits<{
  updated: [l: LessonListResource]
  destroyed: [l: LessonListResource]
}>()

const modelDefaults: LessonResource = {
  id: newId(),
  status: 'planned',
  conducted_at: null,
  is_topic_verified: false,
  is_unplanned: false,
  quarter: null,
  files: [],
}
// тип загружаемого файла
let selectedFileType: LessonFileType = 'homework'

const fileInput = ref()
const saving = ref(false)
const timeMask = { mask: '##:##' }
const { dialog, width } = useDialog('default')
const loading = ref(false)
const itemId = ref<number | undefined>()
const lesson = ref<LessonResource>(clone(modelDefaults))
const deleting = ref(false)
const year = ref<Year>()

function create(groupId: number, y: Year) {
  itemId.value = undefined
  year.value = y
  lesson.value = clone(modelDefaults)
  lesson.value.group_id = groupId
  dialog.value = true
}

async function edit(lessonId: number) {
  itemId.value = lessonId
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<LessonResource>(`lessons/${lessonId}`)
  if (data.value) {
    lesson.value = data.value
    year.value = getAcademicYear(lesson.value.date!)
  }
  loading.value = false
}

async function save() {
  saving.value = true
  const method = itemId.value ? 'put' : 'post'
  const url = itemId.value ? `lessons/${itemId.value}` : 'lessons'
  const { data } = await useHttp<LessonListResource>(url, {
    method,
    body: lesson.value,
  })
  if (data.value) {
    emit('updated', data.value)
    itemUpdated('lesson', data.value.id)
  }
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить урок?')) {
    return
  }
  deleting.value = true
  const { data, status } = await useHttp<LessonListResource>(`lessons/${lesson.value.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    deleting.value = false
  }
  else if (data.value) {
    emit('destroyed', data.value)
    dialog.value = false
    setTimeout(() => deleting.value = false, 300)
  }
}

function selectFile(fileType: LessonFileType) {
  selectedFileType = fileType
  fileInput.value.click()
}

function removeFile(file: LessonFile) {
  const index = lesson.value.files.findIndex(f => f.url === file.url)
  lesson.value.files.splice(index, 1)
}

async function onFileSelected(e: Event) {
  const target = e.target as HTMLInputElement
  if (!target.files?.length) {
    return
  }
  const formData = new FormData()
  const file = target.files[0]
  formData.append('file', file)
  const newFile = reactive<LessonFile>({
    name: file.name,
    size: file.size,
    type: selectedFileType,
  })
  lesson.value.files.push(newFile)
  const { data } = await useHttp<string>(`lessons/upload-file`, {
    method: 'POST',
    body: formData,
  })
  if (data.value) {
    newFile.url = data.value
    console.log('uploaded', data.value)
  }
}

defineExpose({ create, edit })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <div v-if="itemId">
          Редактирование занятия
          <div class="dialog-subheader">
            {{ lesson.user ? formatName(lesson.user) : 'неизвестно' }}
            <template v-if="lesson.created_at">
              {{ formatDateTime(lesson.created_at) }}
            </template>
          </div>
        </div>
        <template v-else>
          Добавить занятие
        </template>
        <div>
          <v-btn
            v-if="itemId && lesson.conducted_at === null"
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
            :loading="saving"
            @click="save()"
          />
        </div>
      </div>
      <UiLoader v-if="loading" />
      <div
        v-else
        class="dialog-body"
      >
        <div>
          <TeacherSelector
            v-model="lesson.teacher_id"
            label="Преподаватель"
          />
        </div>
        <div>
          <UiClearableSelect
            v-model="lesson.cabinet"
            :items="selectItems(CabinetLabel)"
            label="Кабинет"
          />
        </div>
        <div>
          <UiClearableSelect
            v-model="lesson.quarter"
            :items="selectItems(QuarterLabel, ['final' as Quarter])"
            label="Четверть"
          />
        </div>
        <div>
          <v-text-field
            v-model="lesson.price"
            label="Цена"
            type="number"
            hide-spin-buttons
          />
        </div>

        <div class="double-input">
          <UiDateInput
            v-model="lesson.date"
            :year="year"
          />
          <div>
            <v-text-field
              v-model="lesson.time"
              v-maska:[timeMask]
              label="Время"
            />
          </div>
        </div>
        <div>
          <v-textarea
            v-model="lesson.topic"
            label="Тема"
            no-resize
          />
        </div>
        <div>
          <v-textarea
            v-model="lesson.homework"
            label="Домашнее задание"
            no-resize
          />
        </div>
        <div>
          <table v-for="(label, fileType) in LessonFileTypeLabel" :key="fileType" class="dialog-table dialog-table--files">
            <tbody>
              <tr
                v-for="file in lesson.files.filter(e => e.type === fileType)"
                :key="file.url || file.name"
                :class="{ 'opacity-disabled': !file.url }"
              >
                <td>
                  <a :href="file.url" target="_blank">
                    {{ file.name }}
                  </a>
                </td>
                <td>
                  {{ humanFileSize(file.size) }}
                </td>
                <td class="text-right">
                  <v-btn
                    :loading="!file.url"
                    icon="$close"
                    variant="plain"
                    :color="file.url ? 'red' : undefined"
                    :size="48"
                    :ripple="false"
                    @click="removeFile(file)"
                  />
                </td>
              </tr>
              <tr>
                <td colspan="10">
                  <UiIconLink @click="selectFile(fileType)">
                    загрузить {{ label }}
                  </UiIconLink>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div>
          <v-checkbox
            v-model="lesson.is_topic_verified"
            label="Тема подтверждена"
          />
          <v-checkbox
            v-model="lesson.is_unplanned"
            label="Внеплановое"
          />
        </div>
        <input
          ref="fileInput"
          style="display: none"
          type="file"
          @change="onFileSelected"
        >
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.dialog-table--files {
  tr:first-child:not(:last-child) td {
    border-top: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  }
  tr:not(:last-child) td {
    a:not(:hover) {
      color: black !important;
    }
  }
  td {
    &:first-child {
      width: 350px;
    }
    &:nth-child(2) {
      width: 100px;
      padding-left: 16px;
    }
  }
  // отступ между двумя категориями файлов
  &:nth-child(2) tr:first-child:not(:last-child) {
    display: inline-block;
    margin-top: 50px;
  }
}
</style>
