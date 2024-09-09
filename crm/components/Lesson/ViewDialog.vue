<script setup lang="ts">
const { dialog, width } = useDialog('default')

const item = ref<LessonResource>()

async function open(lessonId: number) {
  dialog.value = true
  const { data } = await useHttp<LessonResource>(`lessons/${lessonId}`)
  if (data.value) {
    item.value = data.value
  }
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div v-if="item" class="dialog-body lesson-view pt-5">
        <div v-if="item.quarter">
          <div>Четверть:</div>
          <div>
            {{ QuarterLabel[item.quarter] }}
          </div>
        </div>
        <div>
          <div>
            Дата и время:
          </div>
          <div>
            {{ formatDate(item.date!) }}
            в
            {{ formatTime(item.time!) }}
          </div>
        </div>
        <div>
          <div>Группа:</div>
          <div>
            <RouterLink :to="{ name: 'groups-id', params: { id: item.group_id } }">
              ГР-{{ item.group_id }}
            </RouterLink>
          </div>
        </div>
        <div>
          <div>Преподаватель:</div>
          <div>
            <RouterLink :to="{ name: 'teachers-id', params: { id: item.teacher_id } }">
              {{ formatFullName(item.teacher!) }}
            </RouterLink>
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
            <a v-for="file in item.files" :key="file.url" target="_blank" :href="file.url">
              <FileItem :item="file" />
            </a>
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
}
</style>
