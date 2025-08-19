<script setup lang="ts">
import { orderBy } from 'lodash-es'

interface TeacherBalance {
  teacher: PersonResource
  lessons_planned: number
  lessons_conducted: number
  reports: number
  teacher_services: number
  total: number
  paid_lessons: number
  paid_other: number
  to_pay_lessons: number
  to_pay_other: number
}

type TeacherBalanceField = keyof TeacherBalance

const filters = ref(loadFilters({
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<TeacherBalance>(
  `teacher-balances`,
  filters,
)

const tableFields: Array<{
  title: string
  field: TeacherBalanceField
}> = [
  { title: 'проект<br>(занятия)', field: 'lessons_planned' },
  { title: 'начислено<br>(занятия)', field: 'lessons_conducted' },
  { title: 'начислено<br>(отчёты)', field: 'reports' },
  { title: 'начислено<br>(допуслуги)', field: 'teacher_services' },
  { title: 'начислено<br>(итого)', field: 'total' },
  { title: 'выплачено<br>(офф)', field: 'paid_lessons' },
  { title: 'выплачено<br>(неофф)', field: 'paid_other' },
  { title: 'к выплате<br>(офф)', field: 'to_pay_lessons' },
  { title: 'к выплате<br>(неофф)', field: 'to_pay_other' },
]

const sort = ref<{
  field: keyof TeacherBalance
  direction: 'asc' | 'desc'
}>()

function toggleSort(field: TeacherBalanceField) {
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

function getTotal(field: TeacherBalanceField) {
  return items.value.reduce((carry, x) => carry + (x[field] as number), 0)
}

const sortedItems = computed(() => {
  if (!sort.value) {
    return items.value
  }

  const { field, direction } = sort.value

  return orderBy(items.value, x => x[field], direction)
})

watch(filters.value, () => (sort.value = undefined))
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <v-select
        v-model="filters.year"
        :items="selectItems(YearLabel)"
        label="Учебный год"
        density="comfortable"
      />
    </template>
    <v-table
      fixed-header
      height="calc(100vh - 81px)"
      class="teacher-balances-table"
    >
      <thead>
        <tr>
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
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in sortedItems" :key="item.teacher.id">
          <td width="250">
            <RouterLink :to="{ name: 'teachers-id', params: { id: item.teacher.id } }">
              {{ formatName(item.teacher, 'initials') }}
            </RouterLink>
          </td>
          <td v-for="{ field } in tableFields" :key="field">
            {{ formatPrice(item[field] as number) }}
          </td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td />
          <td v-for="{ field } in tableFields" :key="field">
            {{ formatPrice(getTotal(field)) }}
          </td>
        </tr>
      </tfoot>
    </v-table>
  </UiIndexPage>
</template>

<style lang="scss">
.teacher-balances-table {
  tr {
    td,
    th {
      &:not(:first-child) {
        width: 140px;
      }
      &:nth-child(6),
      &:nth-child(7) {
        border-left: 1px solid rgb(var(--v-theme-border));
      }
    }
    td {
      &:first-child {
        padding-left: var(--padding) !important;
      }
      &:not(:first-child) {
        padding-left: 12px !important;
        padding-right: 12px !important;
      }
      &:nth-child(2) {
        color: rgb(var(--v-theme-gray));
      }
      &:nth-child(6) {
        font-weight: bold !important;
      }
    }
  }
  tfoot {
    tr {
      position: sticky;
      bottom: 0;
    }
    td {
      font-weight: 500;
      border-top: 1px solid rgb(var(--v-theme-border)) !important;
    }
  }
  thead,
  tfoot {
    tr > th,
    tr > td {
      background: rgb(var(--v-theme-bg)) !important;
    }
  }
}
</style>
