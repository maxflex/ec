<script setup lang="ts">
import { cloneDeep } from 'lodash-es'
import { apiUrl, modelDefaults, type WebReviewResource } from '.'

const emit = defineEmits<{
  updated: [item: WebReviewResource, deleted: boolean]
}>()
const { width, dialog } = useDialog('default')

const item = ref<WebReviewResource>(modelDefaults)
const itemId = ref<number>()
const saving = ref(false)
const loading = ref(false)

async function edit(id: number) {
  itemId.value = id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<WebReviewResource>(`${apiUrl}/${id}`)
  if (data.value) {
    item.value = data.value
  }
  loading.value = false
}

function create(clientId: number) {
  itemId.value = undefined
  item.value = cloneDeep({
    ...modelDefaults,
    client_id: clientId,
  })
  dialog.value = true
}

async function save() {
  saving.value = true
  if (item.value.id > 0) {
    const { data } = await useHttp<WebReviewResource>(`${apiUrl}/${item.value.id}`, {
      method: 'put',
      body: item.value,
    })
    if (data.value) {
      item.value = data.value
    }
  }
  else {
    const { data } = await useHttp<WebReviewResource>(apiUrl, {
      method: 'post',
      body: item.value,
    })
    if (data.value) {
      item.value = data.value
    }
  }
  emit('updated', item.value, false)
  dialog.value = false
  setTimeout(() => saving.value = false, 200)
}

function onDeleted() {
  dialog.value = false
  emit('updated', item.value, true)
}

defineExpose({ edit, create })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <div v-if="itemId">
          Редактирование отзыва
          <div class="dialog-subheader">
            <template v-if="item.user && item.created_at">
              {{ formatName(item.user) }}
              {{ formatDateTime(item.created_at) }}
            </template>
          </div>
        </div>
        <template v-else>
          Добавить отзыв
        </template>
        <div>
          <CrudDeleteBtn
            :id="itemId"
            :api-url="apiUrl"
            confirm-text="Вы уверены, что хотите удалить отзыв?"
            @deleted="onDeleted()"
          />
          <v-btn
            :size="48"
            icon="$save"
            variant="text"
            @click="save()"
          />
        </div>
      </div>
      <UiLoader v-if="loading" />
      <div v-else class="dialog-body">
        <div class="text-center pb-2">
          <v-rating
            v-model="item.rating"
            hover
            active-color="orange"
            color="orange"
          />
        </div>
        <div v-if="item.client">
          <v-text-field
            :model-value="formatFullName(item.client)"
            label="Клиент"
            disabled
          />
        </div>
        <div>
          <v-textarea
            v-model="item.text"
            rows="3"
            no-resize
            auto-grow
            label="Текст отзыва"
          />
        </div>
        <div>
          <v-text-field
            v-model="item.signature"
            label="Подпись"
          />
        </div>

        <div>
          <UiMultipleSelect
            v-model="item.programs"
            :items="selectItems(ProgramLabel)"
            label="Программы"
          />
        </div>

        <div>
          <v-checkbox
            v-model="item.is_published"
            label="Опубликован"
          />
        </div>

        <div class="table table--separated mt-6">
          <div v-for="es in item.exam_scores" :key="es.id">
            <div style="width: 320px">
              {{ ExamLabel[es.exam] }}
            </div>
            <div>
              балл: {{ es.score }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </v-dialog>
</template>
