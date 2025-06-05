<script setup lang="ts">
import type { ClientResource, MarkSheet } from '.'
import { apiUrl } from '.'

const { client } = defineProps <{ client: ClientResource }>()

const markSheet = ref<MarkSheet>(client.mark_sheet || {})
const saving = ref(false)

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

async function save() {
  saving.value = true
  await useHttp(`${apiUrl}/${client.id}`, {
    method: 'put',
    body: {
      ...client,
      mark_sheet: Object.keys(markSheet.value).length ? markSheet.value : null,
    },
  })
  saving.value = false
  useGlobalMessage('ведомость сохранена', 'success')
}
</script>

<template>
  <div class="filters">
    <v-spacer />
    <v-btn color="primary" :loading="saving" @click="save()">
      сохранить
    </v-btn>
  </div>
  <v-table class="mark-sheet" hover>
    <tbody>
      <tr v-for="(label, subject) in SubjectLabel" :key="subject" @click="toggleMark(subject)">
        <td width="300">
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
        <td></td>
      </tr>
    </tbody>
  </v-table>
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
