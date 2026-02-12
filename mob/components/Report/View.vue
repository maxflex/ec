<script setup lang="ts">
import { mdiArrowLeftThin } from '@mdi/js'

const route = useRoute()
const item = ref<ReportResource>()
const reportText = computed(() => item.value?.ai_comment || item.value?.comment || '')

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
      Отчёт {{ item.id }}
    </UiPageTitle>
    <div class="report-view">
      <v-card variant="tonal" class="mt-4">
        <template #prepend>
          <UiAvatar :item="item.teacher!" :size="60" class="mr-2" />
        </template>
        <template #title>
          {{ formatName(item.teacher!, 'full') }}
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
          <div>Дата:</div>
          <div>
            {{ formatDate(item.created_at!) }}
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
        <div v-if="reportText">
          <div v-if="!item.ai_comment">
            Текст отчета:
          </div>
          <div
            v-if="item.ai_comment"
            class="report-view__ai-comment"
            v-html="reportText"
          />
          <div v-else>
            {{ reportText }}
          </div>
        </div>
      </div>
      <div v-if="item && item.grade" class="report-view__score" :class="`report-view__score--${item.grade}`">
        <div :class="`text-score text-score--${item.grade}`">
          {{ item.grade }}
        </div>
        <div>
          Оценка<br />
          по отчету
        </div>
      </div>
    </div>
  </template>
</template>

<style lang="scss">
.report-view {
  flex: 1;
  padding: 0 var(--offset) 50px;

  .v-card {
    .v-card-item {
      padding-left: 0 !important;
    }
    .v-card__underlay {
      display: none !important;
    }
  }

  &__score {
    margin-top: 30px;
    width: 160px;
    margin-left: auto;
    margin-right: auto;
    text-align: center;
    text-transform: lowercase;
    padding: 12px;
    border-radius: 8px;
    display: block;

    & > div {
      text-align: center;

      &:first-child {
        font-size: 80px;
        line-height: 80px;
      }

      &:last-child {
        font-size: 16px;
      }
    }
  }

  &__content {
    margin-top: 30px;
    display: flex;
    flex-direction: column;
    gap: 30px;
    font-size: 14px;
    & > div {
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

  &__score {
    &--5 {
      background-color: rgba(var(--v-theme-success), 0.1);
    }

    &--4 {
      background-color: rgba(#e28f2d, 0.1);
      //background-color: #62b44b;
      //background-color: #4cb02f;
      //background-color: #48ad36;
    }

    &--3,
    &--2,
    &--1 {
      background-color: rgba(var(--v-theme-error), 0.1);
    }
  }

  &__client-lessons {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  &__ai-comment {
    white-space: break-spaces;

    p,
    li {
      font-weight: 400;
      margin: 0 0 10px;
    }

    b,
    strong {
      font-weight: 700;
    }

    h3 {
      &:first-child {
        margin-top: 0 !important;
      }
      font-size: 24px;
      line-height: 1.2;
      margin: 30px 0 10px;
      font-weight: 700;
    }

    ul,
    ol {
      margin: 8px 0 12px;
      padding-left: 18px;
    }

    li {
      margin-bottom: 6px;
    }
  }
}
</style>
