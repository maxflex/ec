<script setup lang="ts">
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

const tableFields: Array<{
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

  return [...items.value].sort((a, b) => {
    const fieldA = a[field]
    const fieldB = b[field]

    // Handle cases where field values might be non-truthy
    if (!fieldA && fieldB) {
      return 1 // Non-truthy values go last
    }
    if (fieldA && !fieldB) {
      return -1 // Non-truthy values go last
    }
    if (!fieldA && !fieldB) {
      return 0 // Both are non-truthy, consider them equal
    }

    // Ascending or descending order
    if (direction === 'asc') {
      return fieldA > fieldB ? 1 : fieldA < fieldB ? -1 : 0
    }
    else {
      return fieldA < fieldB ? 1 : fieldA > fieldB ? -1 : 0
    }
  })
})

watch(filters.value, () => {
  reloadData()
  sort.value = undefined
})
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
          <td>
            <RouterLink :to="{ name: 'teachers-id', params: { id: item.teacher.id } }">
              {{ formatNameInitials(item.teacher) }}
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
        width: 150px;
      }
      &:nth-child(6),
      &:nth-child(7) {
        border-left: 1px solid rgb(var(--v-theme-border));
      }
    }
    td {
      &:nth-child(2) {
        color: rgb(var(--v-theme-gray));
      }
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
