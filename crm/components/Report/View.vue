<script setup lang="ts">
const route = useRoute()
const { user } = useAuthStore()
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
    <h2>
      Отчёт от {{ formatDate(item.created_at!) }}
    </h2>
    <div class="report-view__content">
      <div>
        <div>
          Составитель отчёта:
        </div>
        <div>
          <UiPerson :item="item.teacher!" teacher-format="full" />
        </div>
      </div>
      <div>
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
      </div>
      <div>
        <div>Программа:</div>
        <div>
          {{ ProgramLabel[item.program!] }}
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
    </div>
  </div>
  <div v-if="item && item.grade" class="report-view__final">
    <div class="report-view__final-title">
      Оценка:
    </div>
    <span :class="`text-score text-score--${item.grade}`">
      {{ item.grade }}
    </span>
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
      }
    }
  }
  &__final {
    flex: initial !important;
    border-top: 1px solid rgb(var(--v-theme-border));
    margin-top: 20px;
    padding-top: 20px;
    padding-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding-left: 20px;
    &-title {
      font-weight: bold;
      font-size: 20px;
    }
  }
}
</style>
