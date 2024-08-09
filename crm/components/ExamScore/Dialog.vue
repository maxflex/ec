<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  updated: [e: ExamScoreResource ]
  deleted: [e: ExamScoreResource]
}>()
const { width, dialog } = useDialog('default')
const deleting = ref(false)
const saving = ref(false)
const loading = ref(false)
const itemId = ref<number>()
const modelDefaults: ExamScoreResource = {
  id: newId(),
  web_review_id: null,
  year: currentAcademicYear(),
}
const item = ref<ExamScoreResource>(modelDefaults)

function create(clientId: number, year: Year) {
  itemId.value = undefined
  item.value = clone(modelDefaults)
  item.value.client_id = clientId
  item.value.year = year
  dialog.value = true
}

async function edit(id: number) {
  itemId.value = id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<ExamScoreResource>(`exam-scores/${id}`)
  if (data.value) {
    item.value = data.value
  }
  loading.value = false
}

async function save() {
  saving.value = true
  const method = itemId.value ? `put` : `post`
  const url = itemId.value ? `exam-scores/${itemId.value}` : `exam-scores`
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

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить оценку?')) {
    return
  }
  deleting.value = true
  const { data, status } = await useHttp(`exam-scores/${item.value.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    deleting.value = false
  }
  else if (data.value) {
    emit('deleted', item.value)
    dialog.value = false
    setTimeout(() => deleting.value = false, 300)
  }
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
          <v-btn
            v-if="itemId"
            :loading="deleting"
            :size="48"
            class="remove-btn"
            icon="$delete"
            variant="text"
            @click="destroy()"
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
      <UiLoaderr v-if="loading" />
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
      </div>
    </div>
  </v-dialog>
</template>
