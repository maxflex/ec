<script setup lang="ts">
import { cloneDeep } from 'lodash-es'

const emit = defineEmits<{
  save: [cl: ClientLessonResource]
}>()

const commentInputs = ref()
const commentErrors = ref<Record<number, boolean>>({})

const { dialog, width } = useDialog('default')
const item = ref<ClientLessonResource>()
function open(cl: ClientLessonResource) {
  item.value = cloneDeep(cl)
  dialog.value = true
  for (const i of [1, 2, 3]) {
    if (item.value.scores.length < i) {
      item.value.scores.push({ score: null, comment: null })
    }
  }
}

function save() {
  if (!item.value) {
    return
  }
  commentErrors.value = {}
  for (const [i, s] of item.value.scores.entries()) {
    // есть оценка, но нет комментария
    if (s.score && !s.comment) {
      commentInputs.value[i].focus()
      commentErrors.value[i] = true
      return
    }
  }
  dialog.value = false
  emit('save', {
    ...item.value,
    scores: item.value.scores.filter(s => !!s.score),
  })
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        Оценки и комментарии
        <v-btn
          :size="48"
          icon="$save"
          variant="text"
          @click="save()"
        />
      </div>
      <div v-if="item" class="dialog-body">
        <div>
          <v-text-field
            v-model="item.comment"
            label="Общий комментарий"
          />
        </div>
        <div v-for="(s, i) in item.scores" :key="i" class="double-input">
          <UiClearableSelect
            v-model="s.score"
            label="Оценка"
            :items="selectItems(LessonScoreLabel)"
          />
          <v-text-field
            ref="commentInputs"
            v-model="s.comment"
            label="Комментарий к оценке"
            :hide-details="!(i in commentErrors)"
            :error-messages="(i in commentErrors) ? 'введите комментарий к оценке' : undefined"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
