<script setup lang="ts">
import { mdiOpenInNew, mdiVideo } from '@mdi/js'

const { user } = useAuthStore()

const { indexPageData, items } = useIndex<LessonListResource>(
  `lessons`,
  ref({}),
  {
    staticFilters: {
      is_violation: 1,
      teacher_id: user!.id,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <Table>
      <TableRow v-for="item in items" :key="item.id">
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
        <!-- <div style="width: 90px; flex: initial">
          <a v-if="item.violation_video" target="_blank" :href="item.violation_video.url">
            <v-icon :icon="mdiOpenInNew" :size="16" />
            видео
          </a>
        </div> -->
      </TableRow>
    </Table>
  </UiIndexPage>
</template>
