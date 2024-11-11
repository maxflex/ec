<script setup lang="ts">
import {
  mdiBookOpenOutline,
  mdiBookOpenVariant,
  mdiPaperclip,
} from '@mdi/js'

const { item, checkboxes } = defineProps<{
  item: LessonListResource
  checkboxes: { [key: number]: boolean }
}>()

const emit = defineEmits<{
  edit: [id: number]
  view: [id: number]
  conduct: [id: number, status: LessonStatus]
}>()

const isConductDisabled = item.status !== 'conducted'
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
    <div v-else class="table-actionss">
      <v-menu>
        <template #activator="{ props }">
          <v-btn
            icon="$more"
            :size="48"
            variant="text"
            color="gray"
            v-bind="props"
          />
        </template>
        <v-list>
          <v-list-item @click="emit('edit', item.id)">
            редактировать
          </v-list-item>
          <v-list-item
            :disabled="isConductDisabled"
            @click="emit('conduct', item.id, item.status)"
          >
            проводка занятия
          </v-list-item>
        </v-list>
      </v-menu>
    </div>
    <div style="width: 80px; position: relative;" />
    <div style="width: 120px">
      {{ formatTime(item.time) }} – {{ formatTime(item.time_end) }}
    </div>
    <div style="width: 70px">
      <template v-if="item.cabinet">
        {{ CabinetLabel[item.cabinet] }}
      </template>
    </div>
    <div v-if="item.teacher" style="width: 140px">
      <NuxtLink :to="{ name: 'teachers-id', params: { id: item.teacher.id } }" @click.stop>
        {{ formatNameInitials(item.teacher) }}
      </NuxtLink>
    </div>
    <div style="width: 90px">
      <NuxtLink :to="{ name: 'groups-id', params: { id: item.group.id } }" @click.stop>
        ГР-{{ item.group.id }}
      </NuxtLink>
    </div>
    <div style="width: 100px">
      {{ ProgramShortLabel[item.group.program] }}
    </div>
    <div style="width: 70px">
      <span v-if="item.quarter">
        {{ QuarterShortLabel[item.quarter] }}
      </span>
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
    <div style="width: 50px; flex: initial">
      <LessonStatusCircles :item="item" />
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
  &__inline-scores {
    display: flex;
    gap: 4px;
    .score {
      $size: 24px !important;
      width: $size;
      height: $size;
      min-width: $size;
      min-height: $size;
    }
  }
  &--cancelled {
    opacity: 0.4;
  }
  .table-actionss {
    top: -16px;
    right: -1 0px;
  }
  &__checkbox {
    position: absolute;
    right: 0;
  }
}
</style>
