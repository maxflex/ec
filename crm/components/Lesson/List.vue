<script setup lang="ts">
import type { LessonDialog } from '#build/components'

const { group } = defineProps<{ group: GroupResource }>()
const lessons = ref<LessonListResource[]>([])
const lessonDialog = ref<InstanceType<typeof LessonDialog>>()

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

function onLessonUpdated(l: LessonListResource) {
  const index = lessons.value.findIndex(e => e.id === l.id)
  if (index !== -1) {
    lessons.value[index] = l
  }
  else {
    lessons.value.push(l)
    smoothScroll('.v-main', 'bottom')
  }
}

function onLessonDestroyed(l: LessonListResource) {
  const index = lessons.value.findIndex(e => e.id === l.id)
  if (index !== -1) {
    lessons.value.splice(index, 1)
  }
}

nextTick(loadData)
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
      <div style="width: 150px">
        <span class="pl-4">
          {{ LessonStatusLabel[l.status] }}
        </span>
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
    <div style="border: none">
      <a
        class="cursor-pointer"
        @click="() => lessonDialog?.create(group)"
      >
        добавить урок
      </a>
    </div>
  </div>
  <LessonDialog
    ref="lessonDialog"
    @updated="onLessonUpdated"
    @destroyed="onLessonDestroyed"
  />
</template>
