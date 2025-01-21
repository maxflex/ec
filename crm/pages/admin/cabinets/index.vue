<script setup lang="ts">
interface CabinetTeeth {
  cabinet: Cabinet
  teeth: Teeth
}

const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<CabinetTeeth, YearFilters>(
  `cabinets`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <v-select
        v-model="filters.year"
        label="Учебный год"
        :items="selectItems(YearLabel)"
        density="comfortable"
      />
    </template>
    <div class="cabinets">
      <div v-for="item in items" :key="item.cabinet">
        <div class="cabinets__title">
          {{ CabinetLabel[item.cabinet] }}
        </div>
        <TeethBar :items="item.teeth" />
      </div>
    </div>
  </UiIndexPage>
</template>

<style lang="scss">
.cabinets {
  & > div {
    border-bottom: 1px solid rgb(var(--v-theme-border));
    padding: 20px 20px;
    display: flex;
    align-items: center;
    gap: 60px;
    transition: background 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    &:hover {
      background: rgb(var(--v-theme-bg));
    }
  }
  &__title {
    //font-weight: bold;
    //font-size: 22px;
  }
  .teeth {
    //scale: 1.3;
    gap: 16px;
    //&__day--5 {
    //  margin-left: 50px;
    //}
  }
}
</style>
