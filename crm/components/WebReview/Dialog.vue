<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  updated: [item: WebReviewResource, deleted: boolean]
}>()
const { width, dialog } = useDialog('default')

const modelDefaults: WebReviewResource = {
  id: newId(),
  client_id: newId(),
  text: '',
  signature: '',
  is_published: false,
  rating: 0,
  scores: [],
}
const item = ref<WebReviewResource>(modelDefaults)
const saving = ref(false)
const deleting = ref(false)
const editMode = computed(() => item.value.id > 0)

function edit(wr: WebReviewResource) {
  item.value = clone(wr)
  dialog.value = true
}

function create(clientId: number) {
  item.value = clone({
    ...modelDefaults,
    client_id: clientId,
  })
  dialog.value = true
}

async function save() {
  saving.value = true
  if (item.value.id > 0) {
    const { data } = await useHttp<WebReviewResource>(`web-reviews/${item.value.id}`, {
      method: 'put',
      body: item.value,
    })
    if (data.value) {
      item.value = data.value
    }
  }
  else {
    const { data } = await useHttp<WebReviewResource>(`web-reviews`, {
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

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить отзыв?')) {
    return
  }
  deleting.value = true
  await useHttp(`web-reviews/${item.value.id}`, {
    method: 'delete',
  })
  emit('updated', item.value, true)
  dialog.value = false
  setTimeout(() => deleting.value = false, 300)
}

function addScores() {
  item.value.scores.push({
    id: newId(),
  })
  smoothScroll('dialog', 'bottom')
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
        <div v-if="editMode">
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
          <v-btn
            v-if="editMode"
            :loading="deleting"
            :size="48"
            class="remove-btn"
            icon="$delete"
            variant="text"
            @click="destroy()"
          />
          <v-btn
            :size="48"
            icon="$save"
            variant="text"
            @click="save()"
          />
        </div>
      </div>
      <div class="dialog-body">
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
          <v-text-field
            v-model="item.signature"
            label="Подпись"
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
          <v-checkbox
            v-model="item.is_published"
            label="Опубликован"
          />
        </div>
        <div
          v-for="s in item.scores"
          :key="s.id"
          class="double-input web-review-scores"
        >
          <div>
            <UiClearableSelect
              v-model="s.program"
              :items="selectItems(ProgramLabel)"
              label="Программа"
            />
          </div>
          <v-text-field
            v-model="s.score"
            label="балл"
            type="number"
            hide-spin-buttons
          />

          <v-text-field
            v-model="s.max_score"
            label="из"
            type="number"
            hide-spin-buttons
          />
        </div>
        <div>
          <a
            class="cursor-pointer"
            @click="addScores()"
          >
            добавить баллы
          </a>
        </div>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.web-review-scores {
  display: grid;
  grid-template-columns: 3fr 1fr 1fr;
}
</style>
