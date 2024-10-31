<script setup lang="ts">
import type { TestQuestionsDialog } from '#build/components'
import { clone } from 'rambda'

const emit = defineEmits<{
  (e: 'updated'): void
}>()
const modelDefaults: TestResource = {
  id: newId(),
  minutes: 30,
  name: '',
  file: null,
  program: null,
  questions: [],
}
const { dialog, width } = useDialog('default')
const item = ref<TestResource>({ ...modelDefaults })
const input = ref()
const loading = ref(false)
const deleting = ref(false)
const questionsDialog = ref<InstanceType<typeof TestQuestionsDialog>>()

function open(t: TestResource) {
  item.value = clone(t)
  dialog.value = true
}

function create() {
  item.value = { ...modelDefaults }
  dialog.value = true
  nextTick(() => {
    input.value.reset()
    input.value.focus()
  })
}

async function save() {
  loading.value = true
  const { data } = item.value.id > 0
    ? await useHttp(`tests/${item.value.id}`, {
      method: 'PUT',
      body: item.value,
    })
    : await useHttp('tests', {
      method: 'POST',
      body: item.value,
    })
  item.value = data.value as TestResource
  dialog.value = false
  loading.value = false
  emit('updated')
}

function onQuestionsSaved(questions: TestQuestion[]) {
  if (!item.value) {
    return
  }
  item.value.questions = questions
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить тест?')) {
    return
  }
  deleting.value = true
  const { status } = await useHttp(`tests/${item.value.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    deleting.value = false
  }
  else {
    emit('updated')
    dialog.value = false
    setTimeout(() => (deleting.value = false), 300)
  }
}

defineExpose({ open, create })
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
          Редактирование теста
          <div class="dialog-subheader">
            {{ item.user ? formatName(item.user) : 'неизвестно' }}
            <template v-if="item.created_at">
              {{ formatDateTime(item.created_at) }}
            </template>
          </div>
        </div>
        <span v-else> Добавить тест </span>
        <div>
          <v-btn
            v-if="item.id"
            :loading="deleting"
            :size="48"
            class="remove-btn"
            icon="$delete"
            variant="text"
            @click="destroy()"
          />
          <v-btn
            icon="$save"
            :size="48"
            :loading="loading"
            variant="text"
            @click="save()"
          />
        </div>
      </div>
      <div class="dialog-body">
        <div>
          <v-text-field
            ref="input"
            v-model="item.name"
            label="Название"
          />
        </div>
        <div>
          <v-select
            v-model="item.program"
            :items="selectItems(ProgramLabel)"
            label="Программа"
          />
        </div>
        <div>
          <v-text-field
            v-model="item.minutes"
            label="Минут на выполнение"
            type="number"
            hide-spin-buttons
          />
        </div>
        <div>
          <FileUploader v-model="item.file" folder="tests" />
        </div>
        <div>
          <UiIconLink @click="() => questionsDialog?.open(item.questions)">
            редактировать вопросы
            <template v-if="item.questions?.length">
              ({{ item.questions.length }})
            </template>
          </UiIconLink>
        </div>
      </div>
    </div>
  </v-dialog>
  <TestQuestionsDialog
    ref="questionsDialog"
    @saved="onQuestionsSaved"
  />
</template>
