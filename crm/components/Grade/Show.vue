<script setup lang="ts">
const route = useRoute()
const item = ref<GradeResource>()
const changeScore = ref(false)

async function loadData() {
  const { data } = await useHttp<GradeResource>(`grades/${route.params.id}`)
  if (data.value) {
    item.value = data.value
    console.log(data.value)
  }
}

const quarter = ref<Quarter>('q1')
const selected = computed(() => item.value ? item.value.quarters[quarter.value] : undefined)

function setFinalGrade(grade: LessonScore) {
  if (!item.value) {
    return
  }
  item.value.quarters[quarter.value].grade = grade
  useHttp(`grades`, {
    method: 'post',
    body: {
      grade,
      id: item.value.id,
      quarter: quarter.value,
    },
  })
}

nextTick(loadData)
</script>

<template>
  <UiFilters>
    <v-btn
      v-for="(q, key) in QuarterLabel"
      :key="key"
      class="tab-btn"
      :class="{ 'tab-btn--active': quarter === key }"
      variant="plain"
      :ripple="false"
      @click="quarter = key"
    >
      {{ q }}
    </v-btn>
  </UiFilters>
  <div v-if="item && selected" class="grades">
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
        <tr v-for="cl in item.quarters[quarter].client_lessons" :key="cl.id">
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
    <div v-if="selected.grade" class="grades__final">
      <div class="grades__final-grade">
        <div>
          <span v-if="quarter === 'final'">
            Итоговая оценка:
          </span>
          <span v-else>
            Оценка за четверть:
          </span>
        </div>
        <span :class="`score score--${selected.grade}`">
          {{ selected.grade }}
        </span>
        <v-menu close-on-content-click>
          <template #activator="{ props }">
            <v-btn
              icon="$more"
              :size="48"
              variant="text"
              color="gray"
              v-bind="props"
            />
          </template>
          <v-list v-if="changeScore" class="grades__final-selector">
            <v-list-item v-for="(label, score) in LessonScoreLabel" :key="score" @click="setFinalGrade(score)">
              <template #title>
                <span :class="`score score--${score}`" class="mr-2">
                  {{ score }}
                </span>
                {{ label }}
              </template>
            </v-list-item>
          </v-list>
          <v-list v-else>
            <v-list-item @click="changeScore = true">
              изменить оценку
            </v-list-item>
            <v-list-item>
              удалить оценку
            </v-list-item>
          </v-list>
        </v-menu>
      </div>
      <div v-if="selected.teacher" class="text-gray mt-1">
        Поставил <UiPerson :item="selected.teacher" teacher-format="full" />
      </div>
    </div>
    <div v-show="!selected.grade" class="grades__final">
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
