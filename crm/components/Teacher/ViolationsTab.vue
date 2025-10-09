<script setup lang="ts">
import type { LessonDialog } from '#components'
import { mdiVideo } from '@mdi/js'

const { teacherId } = defineProps<{
  teacherId?: number
}>()
const dialog = ref<InstanceType<typeof LessonDialog>>()
const { items, indexPageData } = useIndex<LessonListResource>(
  `lessons`,
  ref({}),
  {
    staticFilters: {
      teacher_id: teacherId,
      is_violation: 1,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <div class="table">
      <div v-for="item in items" :key="item.id">
        <div class="table-actionss">
          <v-btn
            variant="plain"
            icon="$edit"
            :size="48"
            @click="dialog?.edit(item.id)"
          />
        </div>
        <div>
          <v-icon :icon="mdiVideo" :color="item.is_violation ? 'error' : 'success'" />
        </div>
        <div style="width: 80px">
          {{ formatDate(item.date) }}
        </div>
        <div style="width: 120px">
          {{ formatTime(item.time) }} – {{ formatTime(item.time_end) }}
        </div>
        <div style="width: 100px">
          <GroupLink :item="item.group" />
        </div>
        <div style="width: 120px">
          {{ ProgramShortLabel[item.group.program] }}
        </div>
        <div class="text-truncate pr-2" style="flex: 1">
          {{ item.violation_comment }}
        </div>
      </div>
    </div>
  </UiIndexPage>
  <LessonDialog ref="dialog" />
</template>
