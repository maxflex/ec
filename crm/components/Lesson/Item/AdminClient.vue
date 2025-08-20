<script setup lang="ts">
import {
  mdiBookOpenOutline,
  mdiBookOpenVariant,
  mdiPaperclip,
} from '@mdi/js'
import { Cabinets } from '~/components/Cabinet'

const { item } = defineProps<{
  item: LessonListResource
}>()

const emit = defineEmits<{
  edit: [id: number]
  editPrice: [id: number]
  conduct: [id: number, status: LessonStatus]
}>()

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

// можно только редактировать проводку
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
    <div style="width: 110px">
      {{ formatTime(item.time) }} – {{ formatTime(item.time_end) }}
    </div>
    <div style="width: 70px">
      <GroupLink :item="item.group" />
    </div>
    <div v-if="item.teacher" style="width: 150px">
      <NuxtLink :to="{ name: 'teachers-id', params: { id: item.teacher.id } }" @click.stop>
        {{ formatNameInitials(item.teacher) }}
      </NuxtLink>
    </div>
    <div style="width: 110px">
      {{ ProgramShortLabel[item.group.program] }}
    </div>
    <div style="width: 70px">
      <CabinetWithCapacity v-if="item.cabinet" :item="item.cabinet" />
    </div>
    <div style="width: 130px">
      <span v-if="item.client_lesson">
        {{ item.client_lesson.price }} ₽
        -
        №{{ item.client_lesson.contract_id }}
      </span>
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

    <div class="lesson-item__status">
      <template v-if="item.client_lesson">
        <span :class="{ 'text-error': item.client_lesson.status === 'absent' }">
          {{ ClientLessonStatusLabel[item.client_lesson.status] }}
        </span>
      </template>
      <LessonItemStatus v-else :item="item" show-unplanned />
    </div>
    <div style="flex: initial">
      <div v-if="item.client_lesson" class="lesson-item__inline-scores">
        <div v-for="(score, i) in item.client_lesson.scores" :key="i">
          <v-tooltip location="bottom">
            <template #activator="{ props }">
              <span :class="`text-score text-score--${score.score}`" v-bind="props">
                {{ score.score }}
              </span>
            </template>
            {{ score.comment || 'нет комментария' }}
          </v-tooltip>
        </div>
      </div>
    </div>
    <LessonItemSeqQuarter :item="item" />
  </div>
</template>
