<script setup lang="ts">
interface TeacherBalance {
  teacher: PersonResource
  lessons: number
  reports: number
  teacher_payments: number
  teacher_services: number
}

const year = ref<Year>(currentAcademicYear())
const items = ref<TeacherBalance[]>([])

async function loadData() {
  const { data } = await useHttp<TeacherBalance[]>(
      `teacher-balances`,
      {
        params: {
          year: year.value,
        },
      },
  )
  if (data.value) {
    items.value = data.value
  }
}

watch(year, loadData)

nextTick(loadData)
</script>

<template>
  <UiFilters>
    <v-select
      v-model="year"
      :items="selectItems(YearLabel)"
      label="Учебный год"
      density="comfortable"
    />
  </UiFilters>
  <v-table fixed-header height="calc(100vh - 81px)" class="teacher-balances-table">
    <thead>
      <tr>
        <th />
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
          {{ formatPrice(item.lessons) }}
        </td>
        <td>
          {{ formatPrice(item.reports) }}
        </td>
        <td>
          {{ formatPrice(item.teacher_services) }}
        </td>
        <td>
          {{ formatPrice(item.lessons + item.reports + item.teacher_services) }}
        </td>
        <td>
          {{ formatPrice(item.teacher_payments) }}
        </td>
        <td>
          {{ formatPrice(item.lessons + item.reports + item.teacher_services - item.teacher_payments) }}
        </td>
      </tr>
    </tbody>
  </v-table>
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
      &:nth-child(5) {
        font-weight: bold !important;
      }
    }
  }
}
</style>
