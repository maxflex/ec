<script setup lang="ts">
const { items } = defineProps<{
  items: JournalResource[]
}>()
</script>

<template>
  <v-table class="journal">
    <tbody>
      <template v-for="item in items" :key="item.id">
        <tr>
          <td width="120">
            {{ formatDate(item.lesson.date) }}
          </td>
          <td width="150">
            <span v-if="!item.status" class="text-gray">
              до начала обучения
            </span>
            <span v-else :class="{ 'text-error': item.status === 'absent' }">
              {{ ClientLessonStatusLabel[item.status] }}
            </span>
            <template v-if="item.minutes_late">
              <br>
              на {{ item.minutes_late }} мин.
            </template>
          </td>
          <td width="180">
            <UiPerson :item="item.lesson.teacher" />
            <div>
              {{ ProgramShortLabel[item.program] }}
            </div>
          </td>
          <td width="500">
            <div class="journal__data">
              <div v-if="item.lesson.topic">
                <div>
                  Тема:
                </div>
                <div>
                  {{ item.lesson.topic }}
                </div>
              </div>

              <div v-if="item.lesson.homework">
                <div>Домашнее задание:</div>
                <div>
                  {{ item.lesson.homework }}
                </div>
              </div>
              <div v-if="item.lesson.files.length">
                <div />
                <div class="journal__files">
                  <div v-for="f in item.lesson.files" :key="f.url">
                    <FileItem :item="f" downloadable show-size />
                  </div>
                </div>
              </div>
            </div>
          </td>
          <td>
            <div class="journal__data">
              <div v-if="item.scores.length">
                <div>
                  Оценки:
                </div>
                <div class="journal__scores">
                  <div v-for="(score, i) in item.scores" :key="i">
                    <span :class="`text-score text-score--small text-score--${score.score}`">
                      {{ score.score }}
                    </span>
                    – {{ score.comment || 'комментария нет' }}
                  </div>
                </div>
              </div>
              <div v-if="item.comment">
                <div>
                  Комментарий:
                </div>
                <div>
                  {{ item.comment }}
                </div>
              </div>
            </div>
          </td>
        </tr>
      </template>
    </tbody>
  </v-table>
</template>

<style lang="scss">
.journal {
  //tr:first-child td {
  //  background: #f5f5f5;
  //}
  td {
    padding: 16px 16px !important;
    min-height: auto !important;
    vertical-align: top;
  }
  .file-item {
    .v-icon {
      font-size: 32px !important;
    }
  }
  &__files {
    & > div {
      height: 34px;
    }
  }
  &__scores {
    display: flex;
    flex-direction: column;
  }
  &__data {
    display: flex;
    flex-direction: column;
    gap: 16px;
    word-break: break-word;
    & > div {
      display: flex;
      flex-direction: column;
      & > div {
        &:first-child {
          width: 200px;
          font-weight: 500;
        }
        &:last-child {
          flex: 1;
        }
      }
    }
  }
}
</style>
