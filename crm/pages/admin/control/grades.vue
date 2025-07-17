<script setup lang="ts">
interface Fields {
  client_lessons: number[]
  grades: number[]
  reports: number[]
  mark_sheet: number[]
}

type Field = keyof Fields

type Item = PersonResource & Fields

const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}))

const labels: Record<Field, string> = {
  mark_sheet: 'ведомость',
  reports: 'отчёты',
  grades: 'четвертные',
  client_lessons: 'занятия',
}

const { indexPageData, items } = useIndex<Item>(
  `control/grades`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiYearSelector v-model="filters.year" density="comfortable" />
    </template>
    <template #buttons>
      <UiQuestionTooltip>
        На данной странице отображаются клиенты, допущенные ко входу в личный кабинет.
        Доступ закрывается 30 июня {{ filters.year + 1 }} для договоров {{ YearLabel[filters.year] }} или в случае расторжения
      </UiQuestionTooltip>
    </template>
    <v-table class="control-grades table-padding">
      <tbody>
        <tr v-for="item in items" :key="item.id">
          <td width="200">
            <UiPerson :item="item" />
          </td>
          <td>
            <div class="control-grades__items">
              <div v-for="(label, key) in labels" :key="key" class="control-grades__item">
                <div class="control-grades__label">
                  {{ label }}
                </div>
                <div v-if="item[key as Field].length === 0" class="text-gray">
                  оценок нет
                </div>
                <div v-else class="control-grades__grades">
                  <span v-for="(grade, index) in item[key as Field]" :key="index" :class="`text-score text-score--${grade}`">
                    {{ grade }}
                  </span>
                </div>
              </div>
            </div>
          </td>
        </tr>
      </tbody>
    </v-table>
  </UiIndexPage>
</template>

<style lang="scss">
.control-grades {
  &__label {
    width: 150px;
    // color: rgb(var(--v-theme-gray));
  }
  &__grades {
    display: inline-flex;
    column-gap: 8px;
    flex-wrap: wrap;
    flex: 1;
  }
  &__items {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  &__item {
    display: flex;
    // align-items: flex-end;
    // flex-wrap: wrap;
    // column-gap: 8px;
  }
  .text-score {
    line-height: 24px;
    width: 16px;
  }
}
</style>
