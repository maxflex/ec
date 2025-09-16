<script setup lang="ts">
interface Fields {
  lessons_count: number
  absent_count: number
  late_count: number
  online_count: number
}

type Field = keyof Fields

type Item = PersonResource & Fields & {
  directions: ClientDirections
  comments_count: number
}

const year = currentAcademicYear()
const nextYear = year + 1 as Year

const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}))

const tableFields: Array<{
  title: string
  field: Field
  percent?: boolean
}> = [
  { title: 'онлайн', field: 'online_count' },
  { title: 'доля', field: 'online_count', percent: true },
  { title: 'пропусков', field: 'absent_count' },
  { title: 'доля', field: 'absent_count', percent: true },
  { title: 'опозданий', field: 'late_count' },
  { title: 'доля', field: 'late_count', percent: true },
  { title: 'занятий<br/>всего', field: 'lessons_count' },
]

const { indexPageData, items } = useIndex<Item>(
  `control/lessons`,
  filters,
)

function showPercent(item: Item, field: Field): string {
  if (field === 'lessons_count' || item.lessons_count === 0) {
    return ''
  }

  const percent = item[field] as number / item.lessons_count * 100

  if (!percent) {
    return ''
  }

  return `${percent.toFixed(1)}%`
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <v-btn
        v-for="y in [year, nextYear]" :key="y"
        :color="filters.year === y ? 'primary' : 'bg'"
        @click="filters.year = y"
      >
        {{ y }}–{{ y + 1 }}
      </v-btn>
    </template>
    <template #buttons>
      <UiQuestionTooltip>
        На данной странице отображаются клиенты, допущенные ко входу в личный кабинет.
        Доступ закрывается 30 июня {{ filters.year + 1 }} для договоров {{ YearLabel[filters.year] }} или в случае расторжения
      </UiQuestionTooltip>
    </template>
    <v-table
      fixed-header
      height="calc(100vh - 81px)"
      class="control-lessons table-padding"
    >
      <thead>
        <tr>
          <th />
          <th />
          <th v-for="h in tableFields" :key="h.field">
            <span v-html="h.title" />
          </th>
          <th />
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in items" :key="item.id">
          <td width="400">
            <UiPerson :item="item" />
          </td>
          <td>
            <ClientDirections :item="item.directions" />
          </td>
          <td v-for="{ field, percent } in tableFields" :key="`${field}${percent}`" width="110">
            <span v-if="percent" class="text-gray">
              {{ showPercent(item, field) }}
            </span>
            <span v-else>
              {{ formatPrice(item[field] as number) }}
            </span>
          </td>
          <td class="control-lessons__comment">
            <div>
              <CommentBtn
                color="gray"
                :size="42"
                :class="{ 'no-items': item.comments_count === 0 }"
                :count="item.comments_count"
                :entity-id="item.id"
                :entity-type="EntityTypeValue.client"
                extra
              />
            </div>
          </td>
        </tr>
      </tbody>
    </v-table>
  </UiIndexPage>
</template>

<style lang="scss">
.control-lessons {
  td,
  th {
    &:nth-child(2),
    &:nth-child(4),
    &:nth-child(6),
    &:nth-child(8),
    &:nth-child(9) {
      border-right: 1px solid rgb(var(--v-theme-border));
    }

    &:nth-child(3),
    &:nth-child(5),
    &:nth-child(7),
    &:nth-child(9) {
      span {
        display: inline-block;
        padding-left: 10px !important;
      }
    }

    &:nth-child(9) {
      font-weight: 500;
    }
  }

  th {
    vertical-align: middle;
    line-height: 20px;
  }

  &__comment {
    width: 150px;
    position: relative;

    & > div {
      width: 44px;
      position: absolute;
      left: 8px;
      top: 8px;
    }
  }
}
</style>
