<script setup lang="ts">
import { mdiVideo } from '@mdi/js'

const { items, selectable } = defineProps<{
  items: GroupListResource[]
  selectable?: boolean
  // blur групп, где текущий препод больше не ведёт занятия
  // (другими словами, препода нет в планируемых занятиях)
  blurOthers?: boolean
}>()

const emit = defineEmits<{
  selected: [g: GroupListResource]
}>()

const { user } = useAuthStore()

function onClick(g: GroupListResource) {
  if (selectable) {
    emit('selected', g)
  }
}
</script>

<template>
  <div
    class="table table--padding group-list"
    :class="{
      'group-list--selectable table--hover': selectable,
    }"
  >
    <div
      v-for="item in items"
      :id="`group-${item.id}`"
      :key="item.id"
      :class="{
        'group-list__item--blur': blurOthers && !item.teachers.map(e => e.id).includes(user!.id),
      }"
      @click="onClick(item)"
    >
      <div style="width: 150px">
        <NuxtLink :to="{ name: 'groups-id', params: { id: item.id } }">
          Группа {{ item.id }}
        </NuxtLink>
      </div>
      <div style="width: 180px">
        <div v-for="t in item.teachers" :key="t.id">
          <UiPerson :item="t" no-link />
        </div>
      </div>
      <div style="width: 150px">
        {{ ProgramShortLabel[item.program] }}
      </div>
      <div style="width: 120px">
        <template v-if="item.lessons_count">
          {{ item.lessons_count }}
          <span v-if="item.lessons_free_count" class="text-deepOrange">
            + {{ item.lessons_free_count }}
          </span>
          {{ plural(item.lessons_count, ['урок', 'урока', 'уроков'], false) }}
        </template>
      </div>
      <div style="width: 100px">
        <template v-if="item.lessons_conducted_count">
          {{ item.lessons_conducted_count }}
          <span v-if="item.lessons_conducted_free_count" class="text-deepOrange">
            + {{ item.lessons_conducted_free_count }}
          </span>
          {{ plural(item.lessons_conducted_count, ['урок', 'урока', 'уроков'], false) }}
        </template>
        <span v-else-if="item.first_lesson_date" class="text-orange">
          {{ formatDate(item.first_lesson_date) }}
        </span>
      </div>
      <div style="width: 60px">
        {{ item.client_groups_count }} уч.
      </div>
      <div style="width: 50px">
        <v-tooltip v-if="item.zoom.id" location="bottom">
          <template #activator="{ props }">
            <v-icon :icon="mdiVideo" v-bind="props" />
          </template>
          <div>
            ZOOM логин: {{ item.zoom.id }}
          </div>
          <div>
            ZOOM пароль: {{ item.zoom.password }}
          </div>
        </v-tooltip>
      </div>
      <div>
        <TeethAsText :items="item.teeth" />
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.group-list {
  & > div {
    align-items: flex-start !important;
  }
  &--selectable {
    & > div {
      cursor: pointer;
      & > div:first-child {
        width: 120px !important;
      }
    }
  }
  &__item {
    &--blur {
      & > div {
        opacity: 0.4;
      }
    }
  }
}
</style>
