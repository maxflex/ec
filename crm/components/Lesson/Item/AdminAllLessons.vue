<script setup lang="ts">
import {
  mdiBookOpenOutline,
  mdiBookOpenVariant,
  mdiPaperclip,
  mdiSwapHorizontal,
  mdiVideo,
} from '@mdi/js'

const { item } = defineProps<{
  item: LessonListResource
}>()

const emit = defineEmits<{
  edit: [id: number]
  conduct: [id: number, status: LessonStatus]
}>()

const isConductDisabled = item.status !== 'conducted'
</script>

<template>
  <div>
    <div class="table-actionss">
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
    <div style="width: 120px">
      {{ formatTime(item.time) }} – {{ formatTime(item.time_end) }}
    </div>
    <div style="width: 90px">
      <GroupLink :item="item.group" />
    </div>
    <div v-if="item.teacher" style="width: 180px; line-height: 20px;" class="vf-1">
      <NuxtLink :to="{ name: 'teachers-id', params: { id: item.teacher.id } }" @click.stop>
        {{ formatNameInitials(item.teacher) }}
      </NuxtLink>
      <div v-if="item.is_substitute" class="text-gray" style="font-size: 14px">
        замена преподавателя
      </div>
    </div>
    <div style="width: 125px">
      {{ ProgramShortLabel[item.group.program] }}
    </div>
    <div style="width: 60px">
      <GroupStudentsCount v-if="item.status !== 'cancelled'" :item="item" />
    </div>

    <div style="width: 130px" class="lesson-item__icons">
      <div>
        <v-icon v-if="item.topic" :icon="mdiBookOpenOutline" :class="{ 'opacity-3': !item.is_topic_verified }" />
      </div>
      <div>
        <v-icon v-if="item.homework" :icon="mdiBookOpenVariant" />
      </div>
      <div>
        <v-icon v-if="item.has_files" :icon="mdiPaperclip" />
      </div>
      <div>
        <v-icon v-if="item.is_violation !== null" :icon="mdiVideo" :color="item.is_violation ? 'error' : 'success'" />
      </div>
    </div>
    <div style="width: 70px">
      <CabinetWithCapacity v-if="item.cabinet" :item="item.cabinet" />
    </div>
    <div class="lesson-item__status">
      <LessonItemStatus :item="item" show-unplanned show-free />
    </div>
    <LessonItemSeqQuarter :item="item" />
  </div>
</template>
