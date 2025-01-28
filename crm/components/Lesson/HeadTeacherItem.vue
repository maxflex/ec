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
  editPrice: [id: number]
  view: [id: number]
  conduct: [id: number, status: LessonStatus]
}>()

const isConductDisabled = item.status !== 'conducted'

function deleteFromClientLessons() {
  setTimeout(() => {
    if (!confirm(`Удалить ученика из проводки?\nГР-${item.group.id} занятие от ${formatDate(item.date)} в ${formatTime(item.time)}`)) {
      return
    }
    useHttp(`client-lessons/${item.client_lesson!.id}`, {
      method: 'delete',
    })
    item.client_lesson = undefined
  }, 300)
}
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
            редактировать занятие
          </v-list-item>
          <v-list-item :disabled="isConductDisabled" @click="emit('editPrice', item.client_lesson!.id)">
            редактировать цену
          </v-list-item>
          <v-list-item :disabled="isConductDisabled" @click="emit('conduct', item.id, item.status)">
            проводка занятия
          </v-list-item>
          <v-list-item :disabled="isConductDisabled" class="text-error" @click="deleteFromClientLessons()">
            удалить из проводки
          </v-list-item>
        </v-list>
      </v-menu>
    </div>
    <div style="width: 70px; position: relative;" />
    <div style="width: 110px">
      {{ formatTime(item.time) }} – {{ formatTime(item.time_end) }}
    </div>
    <div style="width: 50px">
      <template v-if="item.cabinet">
        {{ CabinetLabel[item.cabinet] }}
      </template>
    </div>
    <div v-if="item.teacher" style="width: 140px">
      <NuxtLink :to="{ name: 'teachers-id', params: { id: item.teacher.id } }" @click.stop>
        {{ formatNameInitials(item.teacher) }}
      </NuxtLink>
    </div>
    <div style="width: 70px">
      <NuxtLink :to="{ name: 'groups-id', params: { id: item.group.id } }" @click.stop>
        ГР-{{ item.group.id }}
      </NuxtLink>
    </div>
    <div style="width: 110px">
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

    <div style="width: 80px">
      <template v-if="item.client_lesson">
        <span :class="{ 'text-error': item.client_lesson.status === 'absent' }">
          {{ ClientLessonStatusLabel[item.client_lesson.status] }}
        </span>
      </template>
      <span v-else class="text-gray">
        {{ LessonStatusLabel[item.status] }}
      </span>
    </div>
    <div style="flex: initial">
      <div v-if="item.client_lesson" class="lesson-item__inline-scores">
        <div v-for="(score, i) in item.client_lesson.scores" :key="i">
          <v-tooltip location="bottom">
            <template #activator="{ props }">
              <span :class="`score score--${score.score}`" v-bind="props">
                {{ score.score }}
              </span>
            </template>
            {{ score.comment }}
          </v-tooltip>
        </div>
      </div>
    </div>
    <div class="text-gray opacity-5 text-right pr-1">
      {{ item.seq }}
      <span v-if="item.quarter">
        / {{ item.quarter[1] }}
      </span>
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
      $width: 25px;
      width: $width;
      max-width: $width;
      min-width: $width;
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
