<script setup lang="ts">
import { mdiArrowLeftThin } from '@mdi/js'

const route = useRoute()
const item = ref<ReportResource>()

async function loadData() {
  const { data } = await useHttp<ReportResource>(
    `reports/${route.params.id}`,
    {
      query: {
        is_published: 1,
      },
    },
  )
  if (data.value) {
    item.value = data.value
  }
}

nextTick(loadData)
</script>

<template>
  <UiLoader v-if="item === undefined" />
  <template v-else>
    <UiFilters>
      <RouterLink :to="{ name: 'reports' }">
        <UiIconLink :icon="mdiArrowLeftThin" prepend>
          все отчеты
        </UiIconLink>
      </RouterLink>
    </UiFilters>
    <UiPageTitle>
      Отчёт от {{ formatDate(item.created_at!) }}
    </UiPageTitle>
    <div class="report-view">
      <v-card variant="tonal" class="mt-4">
        <template #prepend>
          <UiAvatar :item="item.teacher!" :size="60" class="mr-2" />
        </template>
        <template #title>
          {{ formatName(item.teacher!, 'initials') }}
        </template>
        <template #subtitle>
          преподаватель по {{ item.teacher!.subjects.map(s => SubjectDativeLabel[s]).join(' и ') }} <br />
        </template>
      </v-card>
      <div class="report-view__content">
        <div>
          <div>Программа:</div>
          <div>
            {{ ProgramLabel[item.program!] }}
          </div>
        </div>

        <div>
          <div>Посещаемость:</div>
          <div class="report-view__client-lessons">
            <div v-for="cl in item.client_lessons" :key="cl.id">
              <span>
                {{ formatDate(cl.lesson.date) }} –
              </span>
              <span
                :class="{
                  'text-error': cl.status === 'absent',
                  'text-deepOrange': cl.status === 'late' || cl.status === 'lateOnline',
                }"
              >
                {{ ClientLessonStatusLabel[cl.status!] }}
                <span v-if="cl.minutes_late">
                  на {{ cl.minutes_late }} мин.
                </span>
              </span>
              <span v-if="cl.lesson.topic">
                ({{ cl.lesson.topic }})
              </span>
              <span v-if="cl.scores.length">
                Оценки:
                <span v-for="(s, i) in cl.scores" :key="i">
                  <span :class="`text-score text-score--${s.score}`" style="font-size: initial">
                    {{ s.score }}
                  </span>
                  <span v-if="s.comment"> – {{ s.comment }}</span>{{ i + 1 < cl.scores.length ? ', ' : '' }}
                </span>
              </span>
              <span v-if="cl.comment">
                Комментарий от преподавателя: {{ cl.comment }}
              </span>
            </div>

          <!-- {{ date('d.m.Y', strtotime($cl->lesson->date)) }} – @if ($cl->status === \App\Enums\ClientLessonStatus::absent) <b>не был</b> @elseif ($cl->status === \App\Enums\ClientLessonStatus::late) опоздал на {{ $cl->minutes_late }} мин. @else был @endif @if ($cl->lesson->topic)({{ $cl->lesson->topic }})@endif -->
          </div>
        </div>

        <div v-if="item.homework_comment">
          <div>
            Выполнение домашнего задания:
          </div>
          <div>
            {{ item.homework_comment }}
          </div>
        </div>
        <div v-if="item.cognitive_ability_comment">
          <div>
            Способность усваивать материал:
          </div>
          <div>
            {{ item.cognitive_ability_comment }}
          </div>
        </div>
        <div v-if="item.knowledge_level_comment">
          <div>
            Текущий уровень знаний:
          </div>
          <div>
            {{ item.knowledge_level_comment }}
          </div>
        </div>
        <div v-if="item.recommendation_comment">
          <div>
            Рекомендации родителям:
          </div>
          <div>
            {{ item.recommendation_comment }}
          </div>
        </div>
      </div>
    </div>
    <div v-if="item && item.grade" class="report-view__final">
      <div class="report-view__final-title">
        Оценка по отчету:
      </div>
      <span :class="`text-score text-score--${item.grade}`">
        {{ item.grade }}
      </span>
    </div>
  </template>
</template>

<style lang="scss">
.report-view {
  flex: 1;
  padding: 0 var(--offset);
  &__content {
    margin-top: 30px;
    display: flex;
    flex-direction: column;
    gap: 30px;
    font-size: 14px;
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
  &__final {
    flex: initial !important;
    padding-top: 20px;
    padding-bottom: 40px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding-left: 20px;
    &-title {
      font-weight: bold;
      font-size: 20px;
    }
  }

  .v-card__underlay {
    background: rgb(var(--v-theme-gray)) !important;
    // opacity: 1 !important;
  }

  .v-card-title {
    font-weight: bold !important;
    font-size: 14px !important;
  }

  .v-card-item {
    padding: 10px !important;
  }

  .v-card-subtitle {
    white-space: wrap !important;
    padding: 0 !important;
    font-size: 12px !important;
    line-height: 14px !important;
  }
}
</style>
