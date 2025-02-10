<script setup lang="ts">
import { mdiVideo } from '@mdi/js'

const { item } = defineProps<{
  item: LessonListResource
}>()
</script>

<template>
  <div>
    <div style="width: 120px">{{ formatTime(item.time) }} – {{ formatTime(item.time_end) }}</div>
    <div style="width: 80px">
      <template v-if="item.cabinet">
        {{ CabinetAllLabel[item.cabinet] }}
      </template>
    </div>
    <div v-if="item.teacher" style="width: 150px">
      {{ formatNameInitials(item.teacher) }}
    </div>
    <div style="width: 90px">ГР-{{ item.group.id }}</div>
    <div style="width: 140px">
      {{ ProgramShortLabel[item.group.program] }}
    </div>
    <div style="width: 70px">
      <span v-if="item.quarter">
        {{ QuarterShortLabel[item.quarter] }}
      </span>
    </div>
    <div class="lesson-item__status" style="flex: 1">
      <LessonItemStatus :item="item" show-unplanned />
    </div>
    <div style="width: 130px; flex: initial">
      <div>
        {{ item.group.zoom.id }}
      </div>
      <div>
        {{ item.group.zoom.password }}
      </div>
    </div>
  </div>
</template>
