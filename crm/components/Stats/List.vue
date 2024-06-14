<script setup lang="ts">
const { items, mode } = defineProps<{
  items: StatsResource[]
  mode: StatsMode
}>()
</script>

<template>
  <Teleport to=".stats-list__header">
    <div>
      <div>дата</div>
      <div>заявок</div>
      <div>новых договоров</div>
      <div>сумма договоров</div>
    </div>
  </Teleport>
  <div class="table stats-list">
    <div
      v-for="item in items"
      :key="item.date"
    >
      <div>
        {{ formatDateMode(item.date, mode) }}
      </div>
      <div>
        {{ item.requests_count || "" }}
      </div>
      <div>
        {{ item.contracts_count || "" }}
      </div>
      <div>
        {{ item.contracts_sum || "" }}
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.stats-list {
  & > div {
    // &:first-child {
    //   position: sticky;
    //   color: rgb(var(--v-theme-gray));
    // }
    & > div {
      width: 200px;
      &:nth-child(1) {
        width: 200px;
      }
    }
  }
  &__header {
    position: sticky;
    top: 81px;
    background: white;
    z-index: 1;
    color: rgb(var(--v-theme-gray));
  }
}
</style>
