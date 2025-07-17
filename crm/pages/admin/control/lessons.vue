<script setup lang="ts">
interface Fields {
  lessons_count: number
  absent_count: number
  late_count: number
  online_count: number
}

type Field = keyof Fields

type Item = PersonResource & Fields

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
      <UiYearSelector v-model="filters.year" density="comfortable" />
    </template>
    <v-table
      fixed-header
      height="calc(100vh - 81px)"
      class="control-lessons"
    >
      <thead>
        <tr>
          <th />
          <th v-for="h in tableFields" :key="h.field">
            <span v-html="h.title" />
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in items" :key="item.id">
          <td>
            <UiPerson :item="item" />
          </td>
          <td v-for="{ field, percent } in tableFields" :key="`${field}${percent}`" width="130">
            <span v-if="percent" class="text-gray">
              {{ showPercent(item, field) }}
            </span>
            <span v-else>
              {{ formatPrice(item[field] as number) }}
            </span>
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
    &:nth-child(3),
    &:nth-child(5),
    &:nth-child(7) {
      border-right: 1px solid rgb(var(--v-theme-border));
    }

    &:nth-child(4),
    &:nth-child(6),
    &:nth-child(8) {
      span {
        display: inline-block;
        padding-left: 10px !important;
      }
    }
  }
  tbody {
    tr {
      td {
        &:last-child {
          font-weight: 500;
        }
      }
    }
  }
}
</style>
