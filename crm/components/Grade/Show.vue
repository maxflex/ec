<script setup lang="ts">
const route = useRoute()
const item = ref<GradeResource>()

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
    <div class="grades__content">
      <div>
        <div>
          Ученик:
        </div>
        <div>
          <RouterLink :to="{ name: 'teachers-id', params: { id: item.client.id } }">
            {{ formatName(item.client) }}
          </RouterLink>
        </div>
      </div>
      <div>
        <div>Программа:</div>
        <div>
          {{ ProgramLabel[item.program] }}
        </div>
      </div>
      <div v-if="selected.client_lessons.length">
        <div>Посещаемость и пройденные темы:</div>
        <div class="grades__client-lessons">
          <div v-for="cl in item.quarters[quarter].client_lessons" :key="cl.id">
            <div>
              <span class="font-weight-medium">
                {{ formatDate(cl.lesson.date) }}
              </span>
              –
              <span :class="{ 'text-error': cl.status === 'absent' }">
                {{ ClientLessonStatusLabel[cl.status] }}
              </span>
              <template v-if="cl.status !== 'absent'">
                {{ cl.is_remote ? ' удалённо' : ' очно' }}
              </template>
              <template v-if="cl.status === 'late'">
                на {{ cl.minutes_late }} мин.
              </template>
            </div>
            <div>
              <template v-if="cl.lesson.topic">
                {{ cl.lesson.topic }}
              </template>
            </div>
            <div>
              Преподаватель:
              <RouterLink :to="`/teachers/${cl.lesson.teacher.id}`">
                {{ formatFullName(cl.lesson.teacher) }}
              </RouterLink>
            </div>
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
          </div>
        </div>
      </div>
    </div>
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
      </div>
      <div v-if="selected.teacher" class="text-gray mt-1">
        Поставил <UiPerson :item="selected.teacher" teacher-format="full" />
      </div>
    </div>
    <div v-else class="grades__final">
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
  &__content {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 30px;
    & > div {
      display: flex;
      flex-direction: column;
      gap: 2px;
      & > div {
        &:first-child {
          font-weight: bold;
        }
      }
    }
  }
  &__client-lessons {
    margin-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 30px;
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
    margin-top: 20px;
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
