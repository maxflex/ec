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
  select: [id: number]
  conduct: [id: number, status: LessonStatus]
}>()

// можно только редактировать проводку
const isConductDisabled = item.status !== 'conducted'
</script>

<template>
  <div>
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
          <v-list-item @click="emit('select', item.id)">
            выбрать
          </v-list-item>
        </v-list>
      </v-menu>
    </div>
    <div style="width: 120px">
      {{ formatTime(item.time) }} – {{ formatTime(item.time_end) }}
    </div>
    <div style="width: 70px">
      <template v-if="item.cabinet">
        {{ CabinetAllLabel[item.cabinet] }}
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
    <div style="width: 125px">
      {{ ProgramShortLabel[item.group.program] }}
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
    <div style="width: 60px">
      {{ item.group.students_count }} уч.
    </div>
    <div class="lesson-item__status">
      <LessonItemStatus :item="item" show-unplanned show-free />
    </div>
    <div v-if="!Object.keys(checkboxes).length" class="text-gray opacity-5 text-right pr-1">
      {{ item.seq }}
      <span v-if="item.quarter">
        / {{ item.quarter[1] }}
      </span>
    </div>
  </div>
</template>
