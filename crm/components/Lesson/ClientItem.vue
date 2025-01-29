<script setup lang="ts">
import { mdiVideo } from '@mdi/js'

const { item, checkboxes } = defineProps<{
  item: LessonListResource
  checkboxes: { [key: number]: boolean }
}>()
</script>

<template>
  <div
    :id="`lesson-${item.id}`"
    :class="`lesson-item--${item.status}`"
    class="lesson-item"
  >
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
    <div style="width: 120px">
      <LessonStatus2 :item="item" show-label :size="8" />
    </div>
    <div style="flex: initial">
      <UiCircle v-if="item.is_unplanned" color="purple" :size="8">
        внеплановое
      </UiCircle>
    </div>
  </div>
</template>

<style lang="scss">
.lesson-item {
  position: relative;
  &__contract-lesson {
    flex: auto !important;
    display: flex;
    gap: 20px;
    // padding-left: 650px;
  }
  &__icons {
    display: flex;
    align-items: center;
    gap: 4px;
    & > div {
      width: 26px;
    }
  }
  &__scores {
    display: flex;
    flex-direction: column;
    gap: 10px;
    & > div {
      display: flex;
    }
  }
  &--cancelled {
    opacity: 0.4;
  }
  &__checkbox {
    position: absolute;
    right: 0;
  }
}
</style>
