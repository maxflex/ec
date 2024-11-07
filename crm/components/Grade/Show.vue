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
  <div v-if="item && selectedQuarter" class="grades">
    <JournalList :items="selectedQuarter.client_lessons" />
    <div v-if="selectedQuarter.grade" class="grades__final">
      <div class="grades__final-grade">
        <div>
          <span v-if="quarter === 'final'">
            Итоговая оценка:
          </span>
          <span v-else>
            Оценка за четверть:
          </span>
        </div>
        <span :class="`score score--${selectedQuarter.grade.grade}`">
          {{ selectedQuarter.grade.grade }}
        </span>
        <v-menu v-if="!isDisabled" :close-on-content-click="isChangeGradeSubmenu" :width="160">
          <template #activator="{ props }">
            <v-btn
              icon="$more"
              :size="38"
              variant="text"
              color="gray"
              v-bind="props"
              @click="isChangeGradeSubmenu = false"
            />
          </template>
          <v-list v-if="isChangeGradeSubmenu" class="grades__final-selector">
            <v-list-item v-for="(label, score) in LessonScoreLabel" :key="score" @click="updateFinalGrade(score)">
              <template #title>
                <span :class="`score score--${score}`" class="mr-2">
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
      <div v-if="selectedQuarter.grade.teacher" class="text-gray mt-1">
        Поставил <UiPerson :item="selectedQuarter.grade.teacher" teacher-format="full" />
      </div>
    </div>
    <div v-show="!selectedQuarter.grade" class="grades__final">
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
              <span :class="`score score--${score}`" class="mr-2">
                {{ score }}
              </span>
              {{ label }}
            </template>
          </v-list-item>
        </v-list>
      </v-menu>
    </div>
  </div>
  <UiLoader v-else />
</template>

<style lang="scss">
.grades {
  &__final {
    border-top: 1px solid rgb(var(--v-theme-border));
    //margin-top: 20px;
    padding: 20px;
    &-grade {
      display: flex;
      align-items: center;
      gap: 10px;
      & > div:first-child {
        font-weight: bold;
        font-size: 20px;
      }
    }
    &-selector {
      display: flex;
      flex-direction: column-reverse;
    }
  }
}
</style>
