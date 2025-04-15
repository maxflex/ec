<script setup lang="ts">
import { clone } from 'lodash'

const emit = defineEmits<{
  save: [cl: ClientLessonResource]
}>()

const { dialog, width } = useDialog('default')
const item = ref<ClientLessonResource>()
function open(cl: ClientLessonResource) {
  item.value = clone(cl)
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
          <v-text-field v-model="s.comment" label="Комментарий к оценке" />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
