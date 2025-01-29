<script setup lang="ts">
import { mdiVideo } from '@mdi/js'

const { item, checkboxes } = defineProps<{
  item: LessonListResource
  checkboxes: { [key: number]: boolean }
}>()
</script>

<template>
  <div :id="`lesson-${item.id}`" class="lesson-item">
    <div v-if="Object.keys(checkboxes).length" class="lesson-item__checkbox">
      <UiCheckbox :value="checkboxes[item.id]" />
    </div>
    <div style="width: 90px; position: relative;" />
    <div style="width: 120px">
      {{ formatTime(item.time) }} – {{ formatTime(item.time_end) }}
    </div>
    <div style="width: 80px">
      <template v-if="item.cabinet">
        {{ CabinetAllLabel[item.cabinet] }}
      </template>
    </div>
    <div v-if="item.teacher" style="width: 150px">
      {{ formatNameInitials(item.teacher) }}
    </div>
    <div style="width: 90px">
      ГР-{{ item.group.id }}
    </div>
    <div style="width: 140px">
      {{ ProgramShortLabel[item.group.program] }}
    </div>
    <div style="width: 70px">
      <span v-if="item.quarter">
        {{ QuarterShortLabel[item.quarter] }}
      </span>
    </div>
    <div style="width: 70px">
      <v-tooltip location="bottom">
        <template #activator="{ props }">
          <v-icon :icon="mdiVideo" v-bind="props" />
        </template>
        <div>
          ZOOM логин: {{ item.group.zoom.id }}
        </div>
        <div>
          ZOOM пароль: {{ item.group.zoom.password }}
        </div>
      </v-tooltip>
    </div>
    <div style="width: 150px; flex: initial; line-height: 18px; margin-top: 3px">
      <LessonStatus2 :item="item" />
      <div v-if="item.is_unplanned" class="text-purple">
        внеплановое
      </div>
    </div>
  </div>
</template>
