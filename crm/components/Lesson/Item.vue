<script setup lang="ts">
import {
  mdiBookOpenOutline,
  mdiBookOpenVariant,
  mdiCalendarBadge,
  mdiCurrencyUsdOff,
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

// const isConductable = (function () {
//   if (item.status === 'cancelled') {
//     return false
//   }
//   switch (user?.entity_type) {
//     case EntityTypeValue.teacher:
//       return true
//     case EntityTypeValue.client:
//       return false
//     default:
//       return item.status === 'conducted'
//   }
// })()

const isEditable = user?.entity_type !== EntityTypeValue.client

const isClient = user?.entity_type === EntityTypeValue.client
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
          <v-list-item @click="emit('edit', item.id)">
            редактировать
          </v-list-item>
          <v-list-item @click="emit('conduct', item.id, item.status)">
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
    <div style="width: 90px; position: relative;" />
    <div style="width: 120px">
      {{ formatTime(item.time) }} – {{ formatTime(item.time_end) }}
    </div>
    <div style="width: 80px">
      <template v-if="item.cabinet">
        {{ CabinetLabel[item.cabinet] }}
      </template>
    </div>
    <div v-if="item.teacher" style="width: 150px">
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
    <div style="width: 100px">
      <span v-if="item.quarter">
        {{ QuarterLabel[item.quarter] }}
      </span>
    </div>

    <div style="width: 200px" class="lesson-item__icons">
      <div>
        <v-icon v-if="item.topic" :icon="mdiBookOpenOutline" :class="{ 'opacity-3': !item.is_topic_verified }" />
      </div>
      <div>
        <v-icon v-if="item.is_unplanned" :icon="mdiCalendarBadge" />
      </div>
      <div>
        <v-icon v-if="item.is_free" :icon="mdiCurrencyUsdOff" />
      </div>
      <div>
        <v-icon v-if="item.homework" :icon="mdiBookOpenVariant" />
      </div>
      <div>
        <v-icon v-if="item.has_files" :icon="mdiPaperclip" />
      </div>
    </div>

    <div style="width: 120px; flex: initial">
      <LessonStatus2 :status="item.status" />
    </div>
    <!--    TODO: удалить? -->
    <div v-if="item.clientLesson" class="lesson-item__contract-lesson">
      <!-- <div style="width: 240px">
        {{ item.clientLesson.is_remote ? 'удалённо' : 'очно' }}
      </div> -->
      <div style="width: 110px" />
      <div class="lesson-item__scores" style="width: 500px">
        <div v-for="(score, i) in item.clientLesson.scores" :key="i">
          <span :class="`score score--${score.score}`" class="mr-3">
            {{ score.score }}
          </span>
          <div>
            {{ score.comment }}
          </div>
        </div>
      </div>
      <div style="width: 220px">
        {{ item.clientLesson.is_remote ? 'удалённо' : 'очно' }}
      </div>
      <div style="flex: 1">
        <UiCircleStatus
          :class="{
            'text-error': item.clientLesson.status === 'absent',
            'text-warning': item.clientLesson.status === 'late',
            'text-success': item.clientLesson.status === 'present',
          }"
        >
          {{ ClientLessonStatusLabel[item.clientLesson.status] }}
          <template v-if="item.clientLesson.minutes_late">
            на {{ item.clientLesson.minutes_late }} мин.
          </template>
        </UiCircleStatus>
      </div>
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
