<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{ (e: 'updated', i: ClientReviewResource): void }>()
const { width, dialog } = useDialog('default')

const modelDefaults: ClientReviewResource = {
  id: newId(),
  program: 'bio10',
  text: '',
  rating: 0,
}
const item = ref<ClientReviewResource>(modelDefaults)
const saving = ref(false)
const editMode = computed(() => item.value.id > 0)

function edit(i: ClientReviewResource) {
  item.value = clone(i)
  dialog.value = true
}

async function save() {
  saving.value = true
  const { data } = await useHttp<ClientReviewResource>(`client-reviews/${item.value.id}`, {
    method: 'put',
    body: item.value,
  })
  if (data.value) {
    emit('updated', item.value)
  }
  dialog.value = false
  setTimeout(() => saving.value = false, 200)
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
        <div v-if="item.teacher">
          <v-text-field
            :model-value="formatFullName(item.teacher)"
            label="Преподаватель"
            disabled
          />
        </div>
        <div>
          <v-select
            v-model="item.program"
            :items="selectItems(ProgramLabel)"
            label="Программа"
            disabled
          />
        </div>
        <div
          class="double-input"
        >
          <v-text-field
            v-model="item.score"
            label="балл"
            type="number"
            hide-spin-buttons
          />

          <v-text-field
            v-model="item.max_score"
            label="из"
            type="number"
            hide-spin-buttons
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
      </div>
    </div>
  </v-dialog>
</template>
