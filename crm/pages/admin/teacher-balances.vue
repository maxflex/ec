<script setup lang="ts">
import { descend, prop, sortBy } from 'rambda'

interface TeacherBalance {
  teacher: PersonResource
  lessons_planned: number
  lessons_conducted: number
  reports: number
  teacher_payments: number
  teacher_services: number
  total: number
  to_pay: number
}

type TeacherBalanceField = keyof TeacherBalance

const filters = ref({
  year: currentAcademicYear(),
})

const tableHeader: Array<{
  title: string
  field: TeacherBalanceField
}> = [
  { title: 'проект<br>(занятия)', field: 'lessons_planned' },
  { title: 'начислено<br>(занятия)', field: 'lessons_conducted' },
  { title: 'начислено<br>(отчеты)', field: 'reports' },
  { title: 'начислено<br>(допуслуги)', field: 'teacher_services' },
  { title: 'начислено<br>(итого)', field: 'total' },
  { title: 'выплачено', field: 'teacher_payments' },
  { title: 'к выплате', field: 'to_pay' },
]

const sort = ref<{
  field: keyof TeacherBalance
  direction: 'asc' | 'desc'
}>()

const { items, reloadData, indexPageData } = useIndex<TeacherBalance>(
    `teacher-balances`,
    {
      defaultFilters: filters.value,
    },
)

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

  // @ts-expect-error
  const sortFn = direction === 'asc' ? prop(field) : descend(prop(field))

  // @ts-expect-error
  return sortBy(sortFn, items.value)
})

watch(filters.value, reloadData)
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
    <v-table fixed-header height="calc(100vh - 81px)" class="teacher-balances-table">
      <thead>
        <tr>
          <th />
          <th
            v-for="h in tableHeader"
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
          <td>
            <RouterLink
              :to="{
                name: 'teachers-id',
                params: {
                  id: item.teacher.id,
                },
              }"
            >
              {{ formatNameInitials(item.teacher) }}
            </RouterLink>
          </td>
          <td>
            <span class="text-gray">
              {{ formatPrice(item.lessons_planned) }}
            </span>
          </td>
          <td>
            {{ formatPrice(item.lessons_conducted) }}
          </td>
          <td>
            {{ formatPrice(item.reports) }}
          </td>
          <td>
            {{ formatPrice(item.teacher_services) }}
          </td>
          <td>
            {{ formatPrice(item.total) }}
          </td>
          <td>
            {{ formatPrice(item.teacher_payments) }}
          </td>
          <td>
            {{ formatPrice(item.to_pay) }}
          </td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td />
          <td>
            <span class="text-gray">
              {{ formatPrice(getTotal('lessons_planned')) }}
            </span>
          </td>
          <td>
            {{ formatPrice(getTotal('lessons_conducted')) }}
          </td>
          <td>
            {{ formatPrice(getTotal('reports')) }}
          </td>
          <td>
            {{ formatPrice(getTotal('teacher_services')) }}
          </td>
          <td>
            {{ formatPrice(getTotal('total')) }}
          </td>
          <td>
            {{ formatPrice(getTotal('teacher_payments')) }}
          </td>
          <td>
            {{ formatPrice(getTotal('to_pay')) }}
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
        width: 150px;
      }
      &:nth-child(6),
      &:nth-child(7) {
        border-left: 1px solid rgb(var(--v-theme-border));
      }
    }
    td {
      &:nth-child(6) {
        font-weight: bold !important;
      }
    }
  }
  tfoot td {
    font-weight: 500;
    border-top-width: 1px !important;
  }
}
</style>
