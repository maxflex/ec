<script setup lang="ts">
interface TeacherBalance {
  teacher: PersonResource
  lessons_planned: number
  lessons_conducted: number
  reports: number
  teacher_payments: number
  teacher_services: number
}

const filters = ref({
  year: currentAcademicYear(),
})

const { items, reloadData, indexPageData } = useIndex<TeacherBalance>(
    `teacher-balances`,
    {
      defaultFilters: filters.value,
    },
)

function getTotal(field: keyof TeacherBalance) {
  return items.value.reduce((carry, x) => carry + (x[field] as number), 0)
}

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
          <th>
            проект <br>
            (занятия)
          </th>
          <th>
            начислено <br>
            (занятия)
          </th>
          <th>
            начислено <br>
            (отчеты)
          </th>
          <th>
            начислено <br>
            (допуслуги)
          </th>
          <th>
            начислено <br>
            (итого)
          </th>
          <th>
            выплачено
          </th>
          <th>
            к выплате
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in items" :key="item.teacher.id">
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
            {{ formatPrice(item.lessons_conducted + item.reports + item.teacher_services) }}
          </td>
          <td>
            {{ formatPrice(item.teacher_payments) }}
          </td>
          <td>
            {{ formatPrice(item.lessons_conducted + item.reports + item.teacher_services - item.teacher_payments) }}
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
            {{ formatPrice(getTotal('lessons_conducted') + getTotal('reports') + getTotal('teacher_services')) }}
          </td>
          <td>
            {{ formatPrice(getTotal('teacher_payments')) }}
          </td>
          <td>
            {{ formatPrice(getTotal('lessons_conducted')
              + getTotal('reports')
              + getTotal('teacher_services')
              - getTotal('teacher_payments'),
            ) }}
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
