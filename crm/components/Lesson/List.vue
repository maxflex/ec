<script setup lang="ts">
import type { LessonDialog } from '#build/components'

const { group } = defineProps<{ group: GroupResource }>()
const lessons = ref<LessonListResource[]>([])
const lessonDialog = ref<InstanceType<typeof LessonDialog>>()

onMounted(() => loadData())

async function loadData() {
  const { data } = await useHttp<ApiResponse<LessonListResource[]>>('lessons', {
    params: {
      group_id: group.id,
    },
  })
  if (data.value) {
    lessons.value = data.value.data
  }
}

function onLessonUpdated(updatedLesson: LessonListResource) {
  const index = lessons.value.findIndex(l => l.id === updatedLesson.id)
  if (index !== -1) {
    lessons.value[index] = updatedLesson
  }
  else {
    lessons.value.push(updatedLesson)
  }
}
</script>

<template>
  <div class="table table--actions-on-hover lessons">
    <div
      v-for="(l, i) in lessons"
      :key="l.id"
      style="padding-right: 10px !important"
    >
      <div
        class="text-gray"
        style="width: 20px"
      >
        {{ i + 1 }}
      </div>
      <div style="width: 120px">
        {{ formatDate(l.start_at) }}
      </div>
      <div style="width: 90px">
        {{ formatTime(l.start_at) }}
      </div>
      <div style="width: 100px">
        каб. {{ l.cabinet }}
      </div>
      <div style="width: 270px">
        <NuxtLink
          v-if="l.teacher"
          :to="{ name: 'teachers-id', params: { id: l.teacher.id } }"
        >
          {{ formatFullName(l.teacher) }}
        </NuxtLink>
        <span v-else>
          не установлено
        </span>
      </div>
      <div
        style="flex: 1"
        class="text-truncate"
      >
        {{ l.topic }}
      </div>
      <div style="width: 100px">
        {{ LessonStatusLabel[l.status] }}
      </div>
      <div
        style="width: 70px; flex: initial !important"
        class="text-right table-actions"
      >
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          color="gray"
          @click="() => lessonDialog?.edit(l)"
        />
      </div>
    </div>
    <div>
      <a
        class="cursor-pointer"
        @click="() => lessonDialog?.create()"
      >
        добавить урок
      </a>
    </div>
  </div>
  <LessonDialog
    ref="lessonDialog"
    @updated="onLessonUpdated"
  />
</template>
