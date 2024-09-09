<script setup lang="ts">
import { clone } from 'rambda'
import { mdiDownload, mdiFilePdfBox, mdiFileTableBox, mdiImage } from '@mdi/js'

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

function selectFile() {
  fileInput.value.click()
}

function removeFile(file: LessonFile) {
  const index = lesson.value.files.findIndex(f => f.url === file.url)
  lesson.value.files.splice(index, 1)
}

function getIcon(file: LessonFile): LessonFileIcon {
  const index = file.name.lastIndexOf('.')
  const extension = file.name.slice(index + 1)
  switch (extension) {
    case 'pdf':
      return {
        icon: mdiFilePdfBox,
        color: '#e75a5a',
      }
    case 'gif':
    case 'svg':
    case 'png':
    case 'jpg':
    case 'jpeg':
      return {
        icon: mdiImage,
        color: '#BEAC83',
      }
    default:
      return {
        icon: mdiFileTableBox,
        color: '#6caf57',
      }
  }
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
  })
  lesson.value.files.push(newFile)
  const { data } = await useHttp<string>(`lessons/upload-file`, {
    method: 'POST',
    body: formData,
  })
  if (data.value) {
    newFile.url = data.value
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
          <div class="lesson-files">
            <v-menu
              v-for="file in lesson.files"
              :key="file.url || file.name"
            >
              <template #activator="{ props }">
                <div v-bind="props" class="lesson-files__file" :class="{ 'opacity-disabled': !file.url }">
                  <v-icon :icon="getIcon(file).icon" :color="getIcon(file).color" />
                  <span>
                    {{ filterTruncate(file.name, 40) }}
                  </span>
                </div>
              </template>
              <v-list>
                <v-list-item :href="file.url" target="_blank">
                  <template #prepend>
                    <v-icon :icon="mdiDownload" />
                  </template>
                  скачать
                </v-list-item>
                <v-list-item @click="removeFile(file)">
                  <template #prepend>
                    <v-icon icon="$delete" />
                  </template>
                  удалить
                </v-list-item>
              </v-list>
            </v-menu>
            <div class="mt-2">
              <UiIconLink icon="$file" prepend @click="selectFile()">
                прикрепить файл
              </UiIconLink>
            </div>
          </div>
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
.lesson-files {
  display: flex;
  flex-direction: column;
  gap: 10px;
  &__file {
    display: inline-flex;
    align-items: center;
    position: relative;
    cursor: pointer;
    width: fit-content;
    left: -5px;
    .v-icon {
      font-size: 44px;
      margin-right: 8px;
    }
  }
}
</style>
