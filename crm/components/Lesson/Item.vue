<script setup lang="ts">
import { mdiAccountEdit, mdiAccountGroup } from '@mdi/js'

const { item, editable, conductable } = defineProps<{
  item: LessonListResource
  editable?: boolean
  conductable?: boolean
}>()

const emit = defineEmits<{
  edit: [id: number]
  conduct: [id: number, status: LessonStatus]
}>()
</script>

<template>
  <div
    :id="`lesson-${item.id}`"
    :class="`lesson-list__status--${item.status}`"
  >
    <div v-if="editable || conductable" class="table-actionss">
      <v-btn
        v-if="conductable"
        :icon="mdiAccountEdit"
        :size="48"
        variant="text"
        color="gray"
        @click="emit('conduct', item.id, item.status)"
      />
      <v-btn
        v-if="editable"
        icon="$edit"
        :size="48"
        variant="text"
        color="gray"
        @click="emit('edit', item.id)"
      />
    </div>
    <div style="width: 110px; position: relative;">
      <div v-if="editable" style="position: absolute; left: 90px; top: -25px">
        <slot name="checkbox" />
      </div>
    </div>
    <div style="width: 120px">
      {{ formatTime(item.time) }} – {{ formatTime(item.time_end) }}
    </div>
    <div style="width: 80px">
      <template v-if="item.cabinet">
        К–{{ item.cabinet }}
      </template>
    </div>
    <div v-if="item.teacher" style="width: 150px">
      <NuxtLink :to="{ name: 'teachers-id', params: { id: item.teacher.id } }">
        {{ formatNameInitials(item.teacher) }}
      </NuxtLink>
    </div>
    <div style="width: 90px">
      <NuxtLink :to="{ name: 'groups-id', params: { id: item.group.id } }">
        ГР-{{ item.group.id }}
      </NuxtLink>
    </div>
    <div style="width: 120px">
      {{ ProgramShortLabel[item.group.program] }}
    </div>
    <div style="width: 80px; display: flex; align-items: center">
      <v-icon :icon="mdiAccountGroup" class="mr-2 vfn-1" />
      {{ item.group.contracts_count }}
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
    <div v-if="item.clientLesson" class="lesson-list__contract-lesson">
      <!-- <div style="width: 240px">
        {{ item.clientLesson.is_remote ? 'удалённо' : 'очно' }}
      </div> -->
      <div style="width: 110px" />
      <div class="lesson-list__scores" style="width: 500px">
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
.lesson-list {
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
}
</style>
