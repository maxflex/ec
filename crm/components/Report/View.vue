<script setup lang="ts">
import type { ReportResource } from '.'

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
  <div v-if="item" class="panel">
    <div class="panel-info">
      <div class="align-self-center">
        <h2 style="font-size: 28px">
          <template v-if="item.id > 0">
            Отчет {{ item.id }}
          </template>
          <template v-else>
            Новый отчёт
          </template>
        </h2>
      </div>

      <div>
        <div>ученик</div>
        <div>
          <UiPerson :item="item.client!" />
        </div>
      </div>

      <div>
        <div>программа</div>
        <div>
          {{ ProgramLabel[item.program!] }}
        </div>
      </div>

      <div>
        <div>дата</div>
        <div>
          {{ formatDate(item.created_at!) }}
        </div>
      </div>
    </div>
  </div>
  <div v-if="item" class="report-view pa-6">
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
      <div v-if="item && item.grade" class="report-view__score" :class="`report-view__score--${item.grade}`">
        <div :class="`text-score text-score--${item.grade}`">
          {{ item.grade }}
        </div>
        <div>
          Оценка<br /> по отчету
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
  .v-card {
    .v-card-item {
      padding-left: 0 !important;
    }
    .v-card__underlay {
      display: none !important;
    }
  }

  &__score {
    position: absolute;
    top: var(--padding);
    right: var(--padding);
    // border: 1px solid ;
    border-radius: 8px;
    padding: 12px;
    width: 160px;
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
    & > div {
      text-align: center;
      text-transform: lowercase;
      &:last-child {
        font-size: 16px;
      }
      &:first-child {
        font-size: 80px;
        line-height: 80px;
      }
    }
  }
}
</style>
