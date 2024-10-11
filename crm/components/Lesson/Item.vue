<script setup lang="ts">
import { mdiAccountEdit, mdiAccountGroup, mdiEyeOutline } from '@mdi/js'

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

const isConductable = (function () {
  switch (user?.entity_type) {
    case EntityTypeValue.teacher:
      return true
    case EntityTypeValue.client:
      return false
    default:
      return item.status === 'conducted'
  }
})()

const isEditable = user?.entity_type === EntityTypeValue.user

const isClient = user?.entity_type === EntityTypeValue.client
</script>

<template>
  <div
    :id="`lesson-${item.id}`"
    :class="`lesson-item--${item.status}`"
    class="lesson-item"
  >
    <div v-if="Object.keys(checkboxes).length" class="lesson-item__checkbox">
      <v-icon
        v-if="checkboxes[item.id]"
        color="secondary"
        icon="$checkboxOn"
      />
      <v-icon
        v-else
        icon="$checkboxOff"
        class="opacity-6"
      />
    </div>
    <div v-else class="table-actionss">
      <v-btn
        v-if="isConductable"
        :icon="mdiAccountEdit"
        :size="48"
        variant="text"
        color="gray"
        @click.stop="emit('conduct', item.id, item.status)"
      />
      <v-btn
        v-if="isEditable"
        icon="$edit"
        :size="48"
        variant="text"
        color="gray"
        @click.stop="emit('edit', item.id)"
      />
      <v-btn
        v-if="isClient"
        :icon="mdiEyeOutline"
        :size="48"
        variant="text"
        color="gray"
        @click="emit('view', item.id)"
      />
    </div>
    <div style="width: 110px; position: relative;" />
    <div style="width: 120px">
      {{ formatTime(item.time) }} – {{ formatTime(item.time_end) }}
    </div>
    <div style="width: 80px">
      <template v-if="item.cabinet">
        К–{{ item.cabinet }}
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
    <div style="width: 120px">
      {{ ProgramShortLabel[item.group.program] }}
    </div>
    <div style="width: 80px; display: flex; align-items: center">
      <v-icon :icon="mdiAccountGroup" class="mr-2 vfn-1" />
      {{ item.group.students_count }}
    </div>
    <div style="width: 140px">
      <LessonStatus2 :status="item.status" />
    </div>
    <div style="flex: 1">
      <v-chip v-if="item.is_first" class="text-deepOrange">
        первое
      </v-chip>
      <v-chip v-else-if="item.is_unplanned" class="text-deepOrange">
        внеплановое
      </v-chip>
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
