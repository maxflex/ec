<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{ (e: 'updated', i: WebReviewResource): void }>()
const { width, dialog } = useDialog('default')

const modelDefaults: WebReviewResource = {
  id: newId(),
  text: '',
  signature: '',
  is_published: false,
  rating: 0,
  scores: [],
}
const item = ref<WebReviewResource>(modelDefaults)
const saving = ref(false)
const editMode = computed(() => item.value.id > 0)

function edit(i: WebReviewResource) {
  item.value = clone(i)
  dialog.value = true
}

async function save() {
  saving.value = true
  const { data } = await useHttp<WebReviewResource>(`web-reviews/${item.value.id}`, {
    method: 'put',
    body: item.value,
  })
  if (data.value) {
    emit('updated', item.value)
  }
  dialog.value = false
  setTimeout(() => saving.value = false, 200)
}

function addScores() {
  item.value.scores.push({
    id: newId(),
  })
  smoothScroll('dialog', 'bottom')
}

defineExpose({ edit })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <template v-if="editMode">
          Редактирование отзыва
        </template>
        <template v-else>
          Добавить отзыв
        </template>
        <v-btn
          icon="$save"
          :size="48"
          color="#fafafa"
          @click="save()"
        />
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
