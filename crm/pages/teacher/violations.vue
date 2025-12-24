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
    <div class="table">
      <div v-for="item in items" :key="item.id">
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
        <!-- <div style="width: 90px; flex: initial">
          <a v-if="item.violation_video" target="_blank" :href="item.violation_video.url">
            <v-icon :icon="mdiOpenInNew" :size="16" />
            видео
          </a>
        </div> -->
      </div>
    </div>
  </UiIndexPage>
</template>
