<script setup lang="ts">
import { ReportDialog } from '#components'
import { mdiCheckAll } from '@mdi/js'

const route = useRoute()
const id = Number.parseInt(route.params.id as string)
const loading = ref(true)
const items = ref<ReportResource[]>([])
const index = ref<number>(-1)
const item = computed<ReportResource>(() => items.value[index.value])
const reportDialog = ref<InstanceType<typeof ReportDialog>>()

async function loadData() {
  loading.value = true
  const { data } = await useHttp<ReportResource[]>(
    `reports/tabs`,
    {
      params: {
        id: id || undefined,
        ...route.query,
      },
    },
  )
  items.value = data.value!
  index.value = items.value.findIndex(r => r.id === (id || -1))
  loading.value = false
}

watch(index, () => smoothScroll('main', 'top', 'instant'))

nextTick(loadData)
</script>

<template>
  <div v-if="!loading && item">
    <div class="tabs report-tabs__tabs">
      <div
        v-for="(r, i) in items"
        :key="r.id"
        class="tabs-item report-tabs__tabs-item"
        :class="{ 'tabs-item--active': index === i }"
        @click="index = i"
      >
        <template v-if="r.id === -1">
          Новый отчёт
        </template>
        <template v-else>
          Отчёт  {{ i + 1 }}
        </template>
      </div>
    </div>
    <div class="panel">
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
        <div>
          <div>статус</div>
          <div>
            <ReportStatus :status="item.status" />
            <span v-if="item.delivery">
              / {{ ReportDeliveryLabel[item.delivery] }}
            </span>
          </div>
        </div>

        <div class="panel-actions">
          <CommentBtn
            v-if="item.id > 0"
            :key="item.id"
            color="gray"
            :entity-id="item.id"
            :entity-type="EntityTypeValue.report"
          />
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="reportDialog?.open(item)"
          />
        </div>
      </div>
    </div>
    <div :key="index" class="report-view__content report-tabs__content">
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
      <div v-if="item && item.grade" class="report-tabs__score">
        <div :class="`text-score text-score--${item.grade}`">
          {{ item.grade }}
        </div>
        <div>
          Оценка<br /> по отчету
        </div>
      </div>
    </div>
  </div>
  <ReportDialog ref="reportDialog" />
</template>

<style lang="scss">
.report-tabs {
  padding: var(--padding);

  &__tabs {
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

  &__score {
    position: absolute;
    top: var(--padding);
    right: var(--padding);
    // border: 1px solid ;
    border-radius: 8px;
    padding: 12px;
    width: 160px;
    background-color: rgba(var(--v-theme-success), 0.1);
    & > div {
      text-align: center;
      text-transform: lowercase;
      &:last-child {
      }
      &:first-child {
        font-size: 80px;
        line-height: 80px;
      }
    }
  }

  &__content {
    padding: var(--padding);
    margin: 0 !important;
    position: relative;

    .v-card {
      .v-card-item {
        padding-left: 0 !important;
      }
      .v-card__underlay {
        display: none !important;
      }
    }
  }
}
</style>
