<script setup lang="ts">
import { orderBy } from 'lodash-es'

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

const filters = ref<{
  year: Year
  direction: Direction[]
}>({
  year: currentAcademicYear(),
  direction: [],
})

const tableFields: Array<{
  title: string
  field: Field
  percent?: boolean
}> = [
  { title: 'онлайн', field: 'online_count' },
  // { title: 'доля', field: 'online_count', percent: true },
  { title: 'пропусков', field: 'absent_count' },
  // { title: 'доля', field: 'absent_count', percent: true },
  { title: 'опозданий', field: 'late_count' },
  // { title: 'доля', field: 'late_count', percent: true },
  { title: 'занятий всего', field: 'lessons_count' },
]

const grayFields: Partial<Record<Field, boolean>> = {
  online_count: true,
  absent_count: true,
  late_count: true,
}

const { indexPageData, items } = useIndex<Item>(
  `control/lessons`,
  filters,
)

const sort = ref<{
  field: Field
  direction: 'asc' | 'desc'
}>()

function toggleSort(field: Field) {
  if (!sort.value || sort.value.field !== field) {
    sort.value = {
      field,
      direction: 'asc',
    }
  }
  else if (sort.value.direction === 'asc') {
    sort.value.direction = 'desc'
  }
  else {
    sort.value = undefined
  }
}

const sortedItems = computed(() => {
  if (!sort.value) {
    return items.value
  }

  const { field, direction } = sort.value

  return orderBy(items.value, x => x[field], direction)
})

function showPercent(item: Item, field: Field): string {
  if (field === 'lessons_count' || item.lessons_count === 0) {
    return ''
  }

  const percent = item[field] as number / item.lessons_count * 100

  if (!percent) {
    return ''
  }

  return `${Math.round(percent)}%`
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiYearSelector v-model="filters.year" disabled density="comfortable" />
      <UiMultipleSelect
        v-model="filters.direction"
        density="comfortable"
        :items="selectItems(DirectionLabel)"
        label="Направление"
      />
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
          <th
            v-for="h in tableFields"
            :key="h.field"
            class="sortable"
            :class="{
              'sortable--desc': sort?.direction === 'desc',
            }"
            @click="toggleSort(h.field)"
          >
            <span v-html="h.title" />
            <v-icon v-if="sort?.field === h.field" icon="$collapse" />
          </th>
          <th />
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in sortedItems" :key="item.id">
          <td width="300">
            <UiPerson :item="item" />
          </td>
          <td>
            <ClientDirections :items="item.directions" />
          </td>
          <td v-for="{ field, percent } in tableFields" :key="`${field}${percent}`" width="150">
            <span>
              {{ formatPrice(item[field] as number) }}
            </span>
            <span v-if="field in grayFields" class="control-lessons__gray">
              {{ showPercent(item, field) }}
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
    &:nth-child(5),
    &:nth-child(6) {
      border-right: 1px solid rgb(var(--v-theme-border));
    }

    &:nth-child(3),
    &:nth-child(5),
    &:nth-child(7),
    &:nth-child(9) {
      span:first-child {
        display: inline-block;
        padding-left: 10px !important;
      }
    }

    &:nth-child(6) {
      font-weight: 500;
      width: 160px !important;
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

  &__gray {
    color: rgb(var(--v-theme-gray));
    margin-left: 6px;
    font-size: 12px;
  }
}
</style>
