<script setup lang="ts">
const route = useRoute()
const item = ref<QuartersGradesResource>()
const isChangeGradeSubmenu = ref(false)

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
    <v-table>
      <tbody>
        <tr>
          <td colspan="2">
            Ученик:
            <UiPerson :item="item.client" />
          </td>
          <td colspan="10">
            Программа:
            {{ ProgramLabel[item.program] }}
          </td>
        </tr>
        <tr v-for="cl in selectedQuarter.client_lessons" :key="cl.id">
          <td width="120">
            {{ formatDate(cl.lesson.date) }}
          </td>
          <td width="150">
            <span :class="{ 'text-error': cl.status === 'absent' }">
              {{ ClientLessonStatusLabel[cl.status] }}
            </span>
            <template v-if="cl.status !== 'absent'">
              {{ cl.is_remote ? ' удалённо' : ' очно' }}
            </template>
            <template v-if="cl.status === 'late'">
              на {{ cl.minutes_late }} мин.
            </template>
          </td>
          <td width="180">
            <UiPerson :item="cl.lesson.teacher" />
          </td>
          <td>
            <template v-if="cl.lesson.topic">
              {{ cl.lesson.topic }}
            </template>
          </td>
          <td width="300">
            <div v-if="cl.scores.length" class="grades__scores">
              <div v-for="(score, i) in cl.scores" :key="i">
                <span :class="`score score--${score.score}`">
                  {{ score.score }}
                </span>
                <div>
                  {{ score.comment }}
                </div>
              </div>
            </div>
          </td>
        </tr>
      </tbody>
    </v-table>
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
        <v-menu :close-on-content-click="isChangeGradeSubmenu" :width="160">
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
          <v-btn color="primary" v-bind="props">
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
  .v-table {
    tr:first-child td {
      background: #f5f5f5;
    }
    td {
      padding: 16px 16px !important;
      min-height: auto !important;
    }
  }
  &__scores {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 4px;
    & > div {
      display: flex;
      align-items: center;
      gap: 8px;
    }
  }
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
