<script setup lang="ts">
import { clone } from 'lodash-es'
import { API_URL } from '.'

const emit = defineEmits<{
  updated: [e: ExamScoreResource ]
}>()

const { width, dialog } = useDialog('default')
const saving = ref(false)
const loading = ref(false)
const itemId = ref<number>()
const modelDefaults: ExamScoreResource = {
  id: newId(),
  year: currentAcademicYear(),
  is_published: false,
}
const item = ref<ExamScoreResource>(modelDefaults)

function create(clientId: number) {
  itemId.value = undefined
  item.value = clone(modelDefaults)
  item.value.client_id = clientId
  item.value.year = currentAcademicYear()
  dialog.value = true
}

async function edit(id: number) {
  itemId.value = id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<ExamScoreResource>(`${API_URL}/${id}`)
  if (data.value) {
    item.value = data.value
  }
  loading.value = false
}

async function save() {
  saving.value = true
  const method = itemId.value ? `put` : `post`
  const url = itemId.value ? `${API_URL}/${itemId.value}` : API_URL
  const { data } = await useHttp<ExamScoreResource >(url, {
    method,
    body: item.value,
  })
  if (data.value) {
    emit('updated', data.value)
  }
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
}

defineExpose({ create, edit })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <div v-if="itemId">
          Редактировать баллы
          <div class="dialog-subheader">
            <template v-if="item.user && item.created_at">
              {{ formatName(item.user) }}
              {{ formatDateTime(item.created_at) }}
            </template>
          </div>
        </div>
        <template v-else>
          Добавить баллы
        </template>
        <div>
          <DialogDeleteBtn
            :id="itemId"
            :api-url="API_URL"
            confirm-text="Вы уверены, что хотите удалить балл?"
            @deleted="dialog = false"
          />
          <v-btn
            :loading="saving"
            :size="48"
            icon="$save"
            variant="text"
            @click="save()"
          />
        </div>
      </div>
      <UiLoader v-if="loading" />
      <div v-else class="dialog-body">
        <div>
          <v-select
            v-model="item.year"
            label="Учебный год"
            :items="selectItems(YearLabel)"
          />
        </div>
        <div>
          <UiClearableSelect
            v-model="item.exam"
            label="Экзамен"
            :items="selectItems(ExamLabel)"
          />
        </div>
        <div>
          <v-text-field v-model="item.score" label="Балл" type="number" hide-spin-buttons />
        </div>
        <div>
          <v-checkbox
            v-model="item.is_published"
            label="Опубликован"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
