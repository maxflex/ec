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
            <span :class="{ 'text-error': item.status === 'absent' }">
              {{ ClientLessonStatusLabel[item.status] }}
            </span>
            <template v-if="item.status !== 'absent'">
              {{ item.is_remote ? ' удалённо' : ' очно' }}
            </template>
            <template v-if="item.status === 'late'">
              на {{ item.minutes_late }} мин.
            </template>
          </td>
          <td width="180">
            <UiPerson :item="item.lesson.teacher" />
            <div>
              {{ ProgramShortLabel[item.program] }}
            </div>
          </td>
          <td>
            <div class="journal__data">
              <div v-if="item.lesson.topic">
                <div>
                  Тема:
                </div>
                <div>
                  {{ item.lesson.topic }}
                </div>
              </div>
              <div v-if="item.scores.length">
                <div>
                  Оценки:
                </div>
                <div>
                  <div class="journal__scores">
                    <div v-for="(score, i) in item.scores" :key="i">
                      <span :class="`score score--${score.score}`">
                        {{ score.score }}
                      </span>
                      <div>
                        {{ score.comment }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-if="item.lesson.homework">
                <div>Домашнее задание:</div>
                <div>
                  {{ item.lesson.homework }}
                </div>
              </div>
              <div v-if="item.lesson.files.length">
                <div>Файлы:</div>
                <div class="journal__files">
                  <div v-for="f in item.lesson.files" :key="f.url">
                    <FileItem :item="f" />
                  </div>
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
    gap: 10px;
    margin-top: 4px;
    & > div {
      display: flex;
      align-items: center;
      gap: 8px;
    }
  }
  &__data {
    display: flex;
    flex-direction: column;
    gap: 16px;
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
