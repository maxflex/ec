<script setup lang="ts">
import {
  mdiBookOpenOutline,
  mdiBookOpenVariant,
  mdiEyeOutline,
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

const { user } = useAuthStore()

const isEditable = user?.entity_type !== EntityTypeValue.client

const isClient = user?.entity_type === EntityTypeValue.client

const isConductDisabled = item.date > today() || item.status === 'cancelled' || item.teacher.id !== user?.id
</script>

<template>
  <div
    :id="`lesson-${item.id}`"
    class="lesson-item"
  >
    <div v-if="Object.keys(checkboxes).length" class="lesson-item__checkbox">
      <UiCheckbox :value="checkboxes[item.id]" />
    </div>
    <div v-else class="table-actionss">
      <v-menu
        v-if="isEditable"
      >
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
          <v-list-item :disabled="item.teacher.id !== user?.id" @click="emit('edit', item.id)">
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

      <v-btn
        v-if="isClient"
        :icon="mdiEyeOutline"
        :size="48"
        variant="text"
        color="gray"
        @click="emit('view', item.id)"
      />
    </div>
    <div style="width: 70px; position: relative;" />
    <div style="width: 120px">
      {{ formatTime(item.time) }} – {{ formatTime(item.time_end) }}
    </div>
    <div style="width: 60px">
      <template v-if="item.cabinet">
        {{ CabinetAllLabel[item.cabinet] }}
      </template>
    </div>
    <div v-if="item.teacher" style="width: 150px">
      {{ formatNameInitials(item.teacher) }}
    </div>
    <div style="width: 70px">
      <NuxtLink :to="{ name: 'groups-id', params: { id: item.group.id } }" @click.stop>
        ГР-{{ item.group.id }}
      </NuxtLink>
    </div>
    <div style="width: 140px">
      {{ ProgramShortLabel[item.group.program] }}
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
    <div style="width: 150px; flex: initial; line-height: 18px; margin-top: 3px">
      <LessonStatus2 :item="item" />
      <div v-if="item.is_unplanned" class="text-purple">
        внеплановое
      </div>
    </div>
    <div class="text-gray opacity-5 text-right">
      {{ item.seq }}
    </div>
  </div>
</template>
