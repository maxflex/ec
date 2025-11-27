<script setup lang="ts">
const route = useRoute()
const item = ref<QuartersGradesResource>()
const isChangeGradeSubmenu = ref(false)
const { isTeacher, user } = useAuthStore()

async function loadData() {
  const { data } = await useHttp<QuartersGradesResource>(
    `grades/${route.params.id}`,
    {
      params: {
        with: 'client_lessons',
      },
    },
  )
  item.value = data.value!
}

const quarter = ref<Quarter>('q1')
const selectedQuarter = computed(() => item.value ? item.value.quarters[quarter.value] : undefined)
// оценку может менять:
//  – либо владелец последнего занятия в четверти
//  – либо администратор
const isDisabled = computed(() => isTeacher && selectedQuarter.value?.last_teacher_id !== user?.id)

const allScores = computed(() => {
  if (!selectedQuarter.value) {
    return []
  }
  const result = []
  for (const clientLesson of selectedQuarter.value.client_lessons!) {
    for (const score of clientLesson.scores) {
      result.push(score)
    }
  }

  return result
})

async function setFinalGrade(score: LessonScore) {
  if (!item.value) {
    return
  }
  const { data } = await useHttp<GradeResource>(`grades`, {
    method: 'post',
    body: {
      grade: score,
      id: item.value.id,
      quarter: quarter.value,
    },
  })
  selectedQuarter.value!.grade = data.value!
}

async function updateFinalGrade(score: LessonScore) {
  if (!selectedQuarter.value?.grade) {
    return
  }
  const { data } = await useHttp<GradeResource>(
    `grades/${selectedQuarter.value.grade.id}`,
    {
      method: 'put',
      body: {
        grade: score,
        teacher_id: null, // чтобы сбрасывался teacher_id, если оценку изменил админ
      },
    },
  )
  selectedQuarter.value!.grade = data.value!
}

function deleteFinalGrade() {
  if (!selectedQuarter.value?.grade) {
    return
  }
  useHttp<GradeResource>(
    `grades/${selectedQuarter.value.grade.id}`,
    {
      method: 'delete',
    },
  )
  selectedQuarter.value!.grade = null
}

nextTick(loadData)
</script>

<template>
  <div class="tabs">
    <div
      v-for="(label, q) in QuarterLabel"
      :key="q"
      class="tabs-item"
      :class="{ 'tabs-item--active': quarter === q }"
      @click="quarter = q"
    >
      {{ label }}
    </div>
  </div>
  <template v-if="item && selectedQuarter">
    <UiNoData v-if="!selectedQuarter.client_lessons?.length" class="grades__no-data" />
    <JournalList v-else :items="selectedQuarter.client_lessons" />
    <UiContentBlocks v-if="selectedQuarter.client_lessons?.length">
      <div>
        <div>Все оценки на уроках</div>
        <div v-if="allScores.length">
          <div class="journal__scores">
            <div v-for="(score, i) in allScores" :key="i">
              <span :class="`text-score text-score--small text-score--${score.score}`">
                {{ score.score }}
              </span>
              – {{ score.comment || 'комментария нет' }}
            </div>
          </div>
        </div>
        <div v-else class="text-gray">
          оценок нет
        </div>
      </div>
      <div>
        <div class="d-flex align-center">
          <div v-if="quarter === 'final'">
            Итоговая оценка
          </div>
          <div v-else>
            Оценка за четверть
          </div>
        </div>
        <div v-if="selectedQuarter.grade">
          <div v-if="selectedQuarter.grade" class="journal__scores">
            <div>
              <span :class="`text-score text-score--small text-score--${selectedQuarter.grade.grade}`">
                {{ selectedQuarter.grade.grade }}
              </span>
              –
              оценка выставлена <UiPerson :item="selectedQuarter.grade.teacher" teacher-format="full" />
              {{ formatDateTime(selectedQuarter.grade.created_at) }}
            </div>
          </div>
        </div>
        <div v-if="selectedQuarter.grade">
          <v-menu
            :disabled="isDisabled"
            :close-on-content-click="isChangeGradeSubmenu"
            :width="160"
          >
            <template #activator="{ props }">
              <v-btn color="primary" v-bind="props" @click="isChangeGradeSubmenu = false">
                редактировать оценку
              </v-btn>
            </template>
            <v-list v-if="isChangeGradeSubmenu" class="grades__final-selector">
              <v-list-item
                v-for="(label, score) in LessonScoreLabel"
                :key="score"
                @click="updateFinalGrade(score)"
              >
                <template #title>
                  <span :class="`text-score text-score--${score}`" class="mr-2">
                    {{ score }}
                  </span>
                  {{ label }}
                </template>
              </v-list-item>
            </v-list>
            <v-list v-else>
              <v-list-item @click="isChangeGradeSubmenu = true">
                изменить оценку
              </v-list-item>
              <v-list-item @click="deleteFinalGrade()">
                удалить оценку
              </v-list-item>
            </v-list>
          </v-menu>
        </div>
        <div v-else>
          <v-menu>
            <template #activator="{ props }">
              <v-btn color="primary" v-bind="props" :disabled="isDisabled">
                <template v-if="quarter === 'final'">
                  выставить итоговую оценку
                </template>
                <template v-else>
                  выставить оценку
                </template>
              </v-btn>
            </template>
            <v-list class="grades__final-selector">
              <v-list-item
                v-for="(label, score) in LessonScoreLabel"
                :key="score"
                @click="setFinalGrade(score)"
              >
                <template #title>
                  <span :class="`text-score text-score--${score}`" class="mr-2">
                    {{ score }}
                  </span>
                  {{ label }}
                </template>
              </v-list-item>
            </v-list>
          </v-menu>
        </div>
      </div>
    </UiContentBlocks>
  </template>
  <UiLoader v-else />
</template>

<style lang="scss">
.grades {
  &__final {
    position: sticky !important;
    bottom: 0;
    background: white;
    border-top: 1px solid rgb(var(--v-theme-border));
    padding: 0 20px;
    flex: initial !important;
    min-height: 70px;
    display: flex;
    align-items: center;
    &-selector {
      display: flex;
      flex-direction: column-reverse;
    }
    .score {
      cursor: pointer;
      position: relative;
      overflow: hidden;
      &:before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        background-color: transparent;
        height: 100%;
        width: 100%;
        z-index: -1;
        transition: background-color ease-in-out 0.2s;
      }
      &:hover {
        &:before {
          background-color: rgba(black, 0.2);
        }
      }
    }
  }
  &__no-data {
    position: relative !important;
  }
}
.page-grades-id {
  .filters {
    padding: 0 20px !important;
    --height: 70px !important;
  }

  .content-blocks {
    padding-top: 30px;
    padding-bottom: 30px;
  }

  .journal {
    tr:last-child td {
      border-bottom: 1px solid rgb(var(--v-theme-border)) !important;
    }
  }

  .grade-teacher {
    font-weight: normal !important;
  }

  .tabs {
    position: sticky;
    top: 0;
    min-height: 51px;
    z-index: 1;
    background: white;
    a {
      color: black !important;
    }
    &-item {
      background: white;
      & > div {
        text-align: center;
        //&:last-child {
        //  font-size: 13px;
        //}
      }
      &--active {
        background: #e4e4e4 !important;
      }
    }
  }
}
</style>
