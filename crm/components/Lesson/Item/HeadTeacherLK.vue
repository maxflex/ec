<script setup lang="ts">
import {
  mdiBookOpenOutline,
  mdiBookOpenVariant,
  mdiPaperclip,
} from '@mdi/js'
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
    <div style="width: 70px">
      <NuxtLink :to="{ name: 'groups-id', params: { id: item.group.id } }" @click.stop>
        ГР-{{ item.group.id }}
      </NuxtLink>
    </div>
    <div v-if="item.teacher" style="width: 140px">
      {{ formatNameInitials(item.teacher) }}
    </div>
    <div style="width: 110px">
      {{ ProgramShortLabel[item.group.program] }}
    </div>
    <div style="width: 50px">
      <template v-if="item.cabinet">
        {{ Cabinets[item.cabinet].label }}
      </template>
    </div>

    <div style="width: 100px" class="lesson-item__icons">
      <div>
        <v-icon v-if="item.topic" :icon="mdiBookOpenOutline" :class="{ 'opacity-3': !item.is_topic_verified }" />
      </div>
      <div>
        <v-icon v-if="item.homework" :icon="mdiBookOpenVariant" />
      </div>
      <div>
        <v-icon v-if="item.has_files" :icon="mdiPaperclip" />
      </div>
    </div>

    <div class="lesson-item__status">
      <template v-if="item.client_lesson">
        <span :class="{ 'text-error': item.client_lesson.status === 'absent' }">
          {{ ClientLessonStatusLabel[item.client_lesson.status] }}
        </span>
      </template>
      <LessonItemStatus v-else :item="item" show-unplanned />
    </div>
    <div style="flex: initial">
      <div v-if="item.client_lesson" class="lesson-item__inline-scores">
        <div v-for="(score, i) in item.client_lesson.scores" :key="i">
          <v-tooltip location="bottom">
            <template #activator="{ props }">
              <span :class="`text-score text-score--${score.score}`" v-bind="props">
                {{ score.score }}
              </span>
            </template>
            {{ score.comment || 'нет комментария' }}
          </v-tooltip>
        </div>
      </div>
    </div>
    <LessonItemSeqQuarter :item="item" />
  </div>
</template>
