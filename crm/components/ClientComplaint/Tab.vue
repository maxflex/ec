<script setup lang="ts">
import type { ClientComplaintDialog, LessonDialog } from '#components'
import type { ClientComplaintListResource } from '.'
import { apiUrl } from '.'

const { clientId, teacherId } = defineProps<{
  clientId?: number
  teacherId?: number
}>()
const dialog = ref<InstanceType<typeof ClientComplaintDialog>>()
const lessonDialog = ref<InstanceType<typeof LessonDialog>>()
const { items, indexPageData, reloadData } = useIndex<ClientComplaintListResource>(apiUrl, ref({}), {
  staticFilters: {
    teacher_id: teacherId,
    client_id: clientId,
  },
})
const lessons = ref<LessonListResource[]>([])

// неочевидно: у препода подмешиваем занятия с нарушениями в список жалоб
async function loadLessonsWithViolations() {
  if (!teacherId) {
    return
  }
  indexPageData.value.loading = true
  const { data } = await useHttp<ApiResponse<LessonListResource>>(
    `lessons`,
    {
      params: {
        teacher_id: teacherId,
        is_violation: 1,
      },
    },
  )
  lessons.value = data.value!.data
  if (indexPageData.value.noData && lessons.value.length) {
    indexPageData.value.noData = false
  }
  indexPageData.value.loading = false
}

watch(items, loadLessonsWithViolations)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template v-if="clientId" #buttons>
      <v-btn
        color="primary"
        @click="dialog?.create({
          client_id: clientId,
        })"
      >
        добавить жалобу
      </v-btn>
    </template>
    <ClientComplaintList :items="items" @edit="dialog?.edit" />

    <!-- подмешиваем занятия со статусом is_violation -->
    <div class="table">
      <div v-for="lesson in lessons" :key="lesson.id">
        <div class="table-actionss">
          <v-btn
            variant="plain"
            icon="$edit"
            :size="48"
            @click="lessonDialog?.edit(lesson.id)"
          />
        </div>
        <div style="width: 180px">
          <UiPerson :item="lesson.teacher" />
        </div>
        <div style="width: 120px">
          {{ ProgramShortLabel[lesson.group.program] }}
        </div>
        <div class="text-truncate pr-2" style="flex: 1">
          {{ lesson.violation_comment }}
        </div>
        <div style="flex: initial; width: 250px" class="text-gray text-right">
          занятие от {{ formatDate(lesson.date) }}
          {{ formatTime(lesson.time) }} – {{ formatTime(lesson.time_end) }}
        </div>
      </div>
    </div>
  </UiIndexPage>
  <ClientComplaintDialog ref="dialog" v-model="items" @updated="reloadData" />
  <LessonDialog ref="lessonDialog" />
</template>
