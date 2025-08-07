<script setup lang="ts">
import { Cabinets } from '~/components/Cabinet'

const { item } = defineProps<{
  item: LessonListResource
}>()
</script>

<template>
  <div>
    <div style="width: 110px">
      {{ formatTime(item.time) }} – {{ formatTime(item.time_end) }}
    </div>
    <div style="width: 60px">
      <template v-if="item.cabinet">
        {{ Cabinets[item.cabinet].label }}
      </template>
    </div>
    <div v-if="item.teacher" style="width: 150px">
      {{ formatNameInitials(item.teacher) }}
    </div>
    <div style="width: 70px">
      ГР-{{ item.group.id }}
    </div>
    <div style="width: 140px">
      {{ ProgramShortLabel[item.group.program] }}
    </div>
    <div style="width: 60px">
      <span v-if="item.quarter">
        {{ QuarterShortLabel[item.quarter] }}
      </span>
    </div>
    <div class="lesson-item__status" style="flex: 1">
      <LessonItemStatus :item="item" show-unplanned />
    </div>
    <div class="group-list__zoom">
      <template v-if="item.group.zoom?.id">
        {{ item.group.zoom.id }} /
        {{ item.group.zoom.password }}
      </template>
    </div>
  </div>
</template>
