<script setup lang="ts">
import {
  mdiEyeOutline,
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
          <v-list-item
            :disabled="item.teacher.id !== user?.id"
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
      {{ formatNameInitials(item.teacher) }}
    </div>
    <div style="width: 90px" />
    <div style="width: 100px">
      {{ ProgramShortLabel[item.group.program] }}
    </div>
    <div style="width: 100px" />

    <div style="width: 200px" class="lesson-item__icons" />

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
