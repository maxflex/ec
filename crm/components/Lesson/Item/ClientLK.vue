<script setup lang="ts">
import { mdiCamera, mdiVideo } from '@mdi/js'
import { Cabinets } from '~/components/Cabinet'

const { item } = defineProps<{
  item: LessonListResource
}>()

const { isStudent } = useAuthStore()
</script>

<template>
  <div>
    <div style="width: 110px">
      {{ formatTime(item.time) }} – {{ formatTime(item.time_end) }}
    </div>
    <div style="width: 70px">
      ГР-{{ item.group.id }}
    </div>
    <div v-if="item.teacher" style="width: 180px; line-height: 20px;" class="vf-1">
      <NuxtLink :to="{ name: 'teachers-id', params: { id: item.teacher.id } }" @click.stop>
        {{ formatNameInitials(item.teacher) }}
      </NuxtLink>
      <div v-if="item.is_substitute" class="text-gray" style="font-size: 14px">
        замена преподавателя
      </div>
    </div>
    <div style="width: 140px">
      {{ ProgramShortLabel[item.group.program] }}
    </div>
    <div style="width: 90px">
      <template v-if="item.cabinet">
        {{ Cabinets[item.cabinet].label }}
      </template>
    </div>
    <!-- <div style="width: 60px">
      <span v-if="item.quarter">
        {{ QuarterShortLabel[item.quarter] }}
      </span>
    </div> -->
    <div class="lesson-item__status" style="flex: 1">
      <LessonItemStatus :item="item" show-unplanned />
    </div>
    <div style="width: 250px; flex: initial">
      <div v-if="item.group.zoom?.id && item.status !== 'cancelled'" class="d-flex ga-1 align-center">
        <span style="color: #2d8cff">
          zoom:
        </span>
        <span>
          {{ item.group.zoom.id }} / {{ item.group.zoom.password }}
        </span>
      </div>
    </div>
  </div>
</template>
