<script setup lang="ts">
const { dialog, width } = useDialog('default')

const item = ref<LessonResource>()
const clientLesson = ref<ClientLessonResource | null>(null)

async function open(lessonId: number) {
  item.value = undefined
  dialog.value = true
  const { data } = await useHttp< {
    lesson: LessonResource
    clientLesson: ClientLessonResource | null
  }>(`lessons/${lessonId}`)
  if (data.value) {
    item.value = data.value.lesson
    clientLesson.value = data.value.clientLesson
  }
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div v-if="item" class="dialog-body lesson-view pt-5">
        <template v-if="clientLesson">
          <div>
            <div>
              Статус посещения:
            </div>
            <div
              :class="{
                'text-error': clientLesson.status === 'absent',
                'text-warning': clientLesson.status === 'late',
              }"
            >
              {{ ClientLessonStatusLabel[clientLesson.status] }}
              <template v-if="clientLesson.status !== 'absent'">
                {{ clientLesson.is_remote ? 'удалённо' : 'очно' }}
              </template>
              <template v-if="clientLesson.minutes_late">
                на {{ clientLesson.minutes_late }} мин.
              </template>
            </div>
          </div>
          <div v-if="clientLesson.scores.length">
            <div>Оценки:</div>
            <div class="lesson-view__scores">
              <div v-for="(score, i) in clientLesson.scores" :key="i">
                <span :class="`score score--${score.score}`">
                  {{ score.score }}
                </span>
                <div>
                  {{ score.comment }}
                </div>
              </div>
            </div>
          </div>
        </template>

        <div v-if="item.quarter">
          <div>Четверть:</div>
          <div>
            {{ QuarterLabel[item.quarter] }}
          </div>
        </div>

        <div v-if="item.topic">
          <div>
            Тема урока:
          </div>
          <div>
            {{ item.topic }}
          </div>
        </div>
        <div v-if="item.homework">
          <div>
            Домашнее задание:
          </div>
          <div>
            {{ item.homework }}
          </div>
        </div>
        <div v-if="item.files.length">
          <div>
            Файлы:
          </div>
          <div class="mt-1">
            <div v-for="file in item.files" :key="file.url">
              <a target="_blank" :href="file.url">
                <FileItem :item="file" />
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.lesson-view {
  & > div {
    display: flex;
    flex-direction: column;
    gap: 2px;
    & > div {
      &:first-child {
        font-weight: bold;
      }
    }
  }
  &__scores {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 8px;
    & > div {
      display: flex;
      align-items: center;
      gap: 8px;
    }
  }
}
</style>
