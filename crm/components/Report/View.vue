<script setup lang="ts">
const route = useRoute()
// const { user } = useAuthStore()
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
  <div v-else class="report-view pa-6">
    <h2 class="mb-6">
      Отчёт от {{ formatDate(item.created_at!) }}
    </h2>

    <v-card variant="tonal" width="fit-content" class="pr-2">
      <template #prepend>
        <UiAvatar :item="item.teacher!" :size="80" class="mr-3" />
      </template>
      <template #title>
        {{ formatName(item.teacher!, 'full') }}
      </template>
      <template #subtitle>
        преподаватель по {{ item.teacher!.subjects.map(s => SubjectDativeLabel[s]).join(' и ') }} <br />
      </template>
    </v-card>

    <div class="report-view__content">
      <!-- <div>
        <div>Ученик:</div>
        <div>
          <RouterLink
            v-if="user?.entity_type === EntityTypeValue.user"
            :to="{ name: 'clients-id', params: { id: item.client!.id } }"
          >
            {{ formatName(item.client!) }}
          </RouterLink>
          <template v-else>
            {{ formatName(item.client!) }}
          </template>
        </div>
      </div> -->
      <div>
        <div>Программа:</div>
        <div>
          {{ ProgramLabel[item.program!] }}
        </div>
      </div>

      <div>
        <div>Посещаемость и пройденные темы:</div>
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
          Способность усваивать новый материал:
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
      <div v-if="item && item.grade" class="report-view__score">
        <div class="d-flex align-center ga-2">
          Оценка по отчету:
          <span :class="`text-score text-score--${item.grade}`">
            {{ item.grade }}
          </span>
        </div>
        <div>
        </div>
      </div>
    </div>
    <!-- <div class="mt-12">
      <div></div>
      <div class="text-gray" style="font-size: 14px">
        Отчет написан {{ formatDate(item.created_at!) }}
      </div>
    </div> -->
  </div>
</template>

<style lang="scss">
.report-view {
  flex: 1;
  &__content {
    margin-top: 30px;
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
        &:last-child {
          // word-wrap: break-word;
          // white-space: pre-wrap;
          // max-width: 1000px;
        }
      }
    }
  }

  &__client-lessons {
    & > div {
      // display: flex;
    }
  }

  .text-score,
  &__score {
    font-size: 24px;
  }

  .v-card__underlay {
    background: rgb(var(--v-theme-gray)) !important;
    // opacity: 1 !important;
  }

  .v-card-title {
    font-weight: bold !important;
    font-size: 16px !important;
  }

  .v-card-subtitle {
    font-size: 16px !important;
  }
}
</style>
