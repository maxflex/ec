<script setup lang="ts">
interface CabinetTeeth {
  cabinet: Cabinet
  teeth: Teeth
  free_until: string | null
  is_busy: boolean
}
const { items, indexPageData } = useIndex<CabinetTeeth, YearFilters>(
  `cabinets`,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <div class="cabinets">
      <div v-for="item in items" :key="item.cabinet">
        <div class="cabinets__title">
          {{ CabinetAllLabel[item.cabinet] }}
        </div>
        <div>
          <UiCircle v-if="item.is_busy" color="error">
            занят
          </UiCircle>
          <UiCircle v-else color="success">
            свободен
          </UiCircle>
        </div>
        <div>
          <template v-if="!item.is_busy">
            <template v-if="item.free_until">
              до {{ formatTime(item.free_until) }}
            </template>
            <template v-else>
              до конца дня
            </template>
          </template>
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
    padding: 16px 20px;
    display: flex;
    align-items: center;
    transition: background 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    &:hover {
      background: rgb(var(--v-theme-bg));
    }
    & > div {
      &:first-child {
        width: 150px;
      }
      &:nth-child(2) {
        width: 180px;
      }
      &:nth-child(3) {
        flex: 1;
      }
      &:last-child {
        width: 500px;
      }
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
