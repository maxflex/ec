<script setup lang="ts">
import type { GroupResource } from '~/components/Group'
import {
  mdiBookOpenOutline,
  mdiBookOpenVariant,
  mdiPaperclip,
} from '@mdi/js'
import { Cabinets } from '~/components/Cabinet'

const { item, group } = defineProps<{
  item: LessonListResource
  group?: GroupResource
}>()

const emit = defineEmits<{
  edit: [id: number]
  conduct: [id: number, status: LessonStatus]
}>()

const { user } = useAuthStore()

// препод может редактировать только свои занятия
const isEditDisabled = item.teacher.id !== user?.id

const isConductDisabled = isEditDisabled || item.status === 'cancelled'
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
          <v-list-item :disabled="isEditDisabled" @click="emit('edit', item.id)">
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
    <div v-if="!group" style="width: 70px">
      <NuxtLink :to="{ name: 'groups-id', params: { id: item.group.id } }" @click.stop>
        ГР-{{ item.group.id }}
      </NuxtLink>
    </div>
    <div v-if="item.teacher" style="width: 150px">
      {{ formatNameInitials(item.teacher) }}
    </div>
    <div style="width: 140px">
      {{ ProgramShortLabel[item.group.program] }}
    </div>
    <div style="width: 60px">
      <GroupStudentsCount v-if="item.status !== 'cancelled'" :item="item" />
    </div>
    <div style="width: 60px">
      <template v-if="item.cabinet">
        {{ Cabinets[item.cabinet].label }}
      </template>
    </div>
    <div style="width: 70px">
      <span v-if="item.quarter">
        {{ QuarterShortLabel[item.quarter] }}
      </span>
    </div>
    <div style="width: 90px" class="lesson-item__icons">
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
      <LessonItemStatus :item="item" show-unplanned />
    </div>
    <LessonItemSeqQuarter :item="item" hide-quarter />
  </div>
</template>
