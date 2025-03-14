<script setup lang="ts">
import type { MarkSheet } from '.'

const emit = defineEmits<{
  save: [ms: MarkSheet | null]
}>()

const { dialog, width, transition } = useDialog('default')
const markSheet = ref<MarkSheet>({})

function open(ms: MarkSheet | null) {
  markSheet.value = ms || {}
  dialog.value = true
}

function toggleMark(subject: Subject) {
  const mark = markSheet.value[subject]
  if (mark) {
    const newMark = mark - 1
    if (newMark === 1) {
      delete markSheet.value[subject]
    }
    else {
      markSheet.value[subject] = newMark
    }
  }
  else {
    markSheet.value[subject] = 5
  }
}

function save() {
  emit('save', Object.keys(markSheet.value).length ? markSheet.value : null)
  dialog.value = false
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width" :transition="transition">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        Ведомость оценок
        <v-btn icon="$save" size="48" variant="text" @click="save()" />
      </div>
      <div class="dialog-body pt-0">
        <v-table class="mark-sheet" hover>
          <tbody>
            <tr v-for="(label, subject) in SubjectLabel" :key="subject" @click="toggleMark(subject)">
              <td>
                {{ label }}
              </td>
              <td width="120" class="mark-sheet__mark">
                <span v-if="markSheet[subject]" :class="`text-score text-score--${markSheet[subject]}`">
                  {{ markSheet[subject] }}
                </span>
                <span v-else class="text-gray">
                  нет оценки
                </span>
              </td>
            </tr>
          </tbody>
        </v-table>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.mark-sheet {
  tr {
    cursor: pointer;
    user-select: none;
    // скрыть сочинение
    &:last-child {
      display: none;
    }
  }
  &__mark {
    text-align: center;
    .text-score {
      cursor: pointer !important;
    }
  }
}
</style>
