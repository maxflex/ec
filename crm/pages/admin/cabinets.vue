<script setup lang="ts">
import { Cabinets } from '~/components/Cabinet'

interface CabinetTeeth {
  cabinet: string
  teeth: Teeth
  free_until: string | null
  busy_by: {
    teacher: PersonResource
    program: Program
  } | null
}

const filters = ref<YearFilters>({
  year: currentAcademicYear(),
})

const { items, indexPageData } = useIndex<CabinetTeeth>(
  `cabinets`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiYearSelector v-model="filters.year" density="comfortable" />
    </template>
    <div class="cabinets">
      <div v-for="item in items" :key="item.cabinet">
        <div class="cabinets__title">
          {{ Cabinets[item.cabinet].label }}
        </div>
        <div>
          max:
          {{ Cabinets[item.cabinet].capacity }}
        </div>
        <div>
          <UiCircle v-if="item.busy_by" color="error">
            занят
          </UiCircle>
          <UiCircle v-else color="success">
            свободен
          </UiCircle>
        </div>
        <template v-if="item.busy_by">
          <div>
            <UiPerson :item="item.busy_by.teacher" />
          </div>
          <div>
            {{ ProgramShortLabel[item.busy_by.program] }}
          </div>
        </template>
        <template v-else>
          <div>
            <template v-if="item.free_until">
              до {{ formatTime(item.free_until) }}
            </template>
            <template v-else>
              до конца дня
            </template>
          </div>
          <div />
        </template>
        <TeethBar :items="item.teeth" />
      </div>
    </div>
  </UiIndexPage>
</template>

<style lang="scss">
.cabinets {
  & > div {
    border-bottom: 1px solid rgb(var(--v-theme-border));
    padding: 16px 20px;
    display: flex;
    align-items: center;
    transition: background 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    &:hover {
      background: rgb(var(--v-theme-bg));
    }
    & > div {
      &:first-child {
        width: 100px;
      }
      &:nth-child(2) {
        width: 150px;
      }
      &:nth-child(3) {
        width: 180px;
      }
      &:nth-child(4) {
        width: 230px;
      }
      &:nth-child(5) {
        width: 180px;
      }
    }
  }
  &__title {
    //font-weight: bold;
    //font-size: 22px;
  }
}
</style>
