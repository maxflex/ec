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
  <UiFilters>
    <v-btn
      v-for="(label, q) in QuarterLabel"
      :key="q"
      class="tab-btn"
      :class="{ 'tab-btn--active': quarter === q }"
      variant="plain"
      :ripple="false"
      @click="quarter = q"
    >
      {{ label }}
    </v-btn>
  </UiFilters>
  <template v-if="item && selectedQuarter">
    <UiNoData v-if="!selectedQuarter.client_lessons?.length" class="grades__no-data" />
    <JournalList v-else :items="selectedQuarter.client_lessons" />
    <div v-if="selectedQuarter.grade" :key="selectedQuarter.grade.id" class="grades__final">
      <div class="dialog-section__title mt-0">
        <span v-if="quarter === 'final'">
          Итоговая оценка:
        </span>
        <span v-else>
          Оценка за четверть:
        </span>
      </div>
      <v-menu
        :disabled="isDisabled"
        :close-on-content-click="isChangeGradeSubmenu"
        :width="160"
      >
        <template #activator="{ props }">
          <v-btn
            v-bind="props"
            :size="48"
            @click="isChangeGradeSubmenu = false"
          >
            <span :class="`text-score text-score--${selectedQuarter.grade.grade}`" class="no-pointer-events">
              {{ selectedQuarter.grade.grade }}
            </span>
          </v-btn>
        </template>
        <v-list v-if="isChangeGradeSubmenu" class="grades__final-selector">
          <v-list-item v-for="(label, score) in LessonScoreLabel" :key="score" @click="updateFinalGrade(score)">
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
      <div v-if="selectedQuarter.grade.teacher" class="text-gray pl-2 flex-1-0 text-right">
        <UiPerson :item="selectedQuarter.grade.teacher" teacher-format="full" />
        {{ formatDateTime(selectedQuarter.grade.created_at) }}
      </div>
    </div>
    <div v-else class="grades__final">
      <v-menu>
        <template #activator="{ props }">
          <v-btn
            color="primary"
            v-bind="props"
            :disabled="isDisabled"
          >
            <template v-if="quarter === 'final'">
              выставить итоговую оценку
            </template>
            <template v-else>
              выставить оценку за {{ QuarterLabel[quarter] }}
            </template>
          </v-btn>
        </template>
        <v-list class="grades__final-selector">
          <v-list-item v-for="(label, score) in LessonScoreLabel" :key="score" @click="setFinalGrade(score)">
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
}
</style>
