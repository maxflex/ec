<script setup lang="ts">
import type { LessonDialog } from '#components'
import { mdiOpenInNew, mdiVideo } from '@mdi/js'

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
    <Table>
      <TableRow v-for="item in items" :key="item.id">
        <div class="table-actionss">
          <v-btn
            variant="plain"
            icon="$edit"
            :size="48"
            @click="dialog?.edit(item.id)"
          />
        </div>
        <TableCol :width="30">
          <v-icon :icon="mdiVideo" :color="item.is_violation ? 'error' : 'success'" />
        </TableCol>
        <TableCol :width="80">
          {{ formatDate(item.date) }}
        </TableCol>
        <TableCol :width="120">
          {{ formatTime(item.time) }} – {{ formatTime(item.time_end) }}
        </TableCol>
        <TableCol :width="100">
          <GroupLink :item="item.group" />
        </TableCol>

        <TableCol :width="120">
          {{ ProgramShortLabel[item.group.program] }}
        </TableCol>
        <TableCol class="text-truncate pr-2">
          {{ item.violation_comment }}
        </TableCol>
        <!-- <div style="width: 150px; flex: initial">
          <a v-if="item.violation_video" target="_blank" :href="item.violation_video.url">
            <v-icon :icon="mdiOpenInNew" :size="16" />
            видео
          </a>
        </div> -->
      </TableRow>
    </Table>
  </UiIndexPage>
  <LessonDialog ref="dialog" />
</template>
