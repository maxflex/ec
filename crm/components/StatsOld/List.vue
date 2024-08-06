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
      <div>новых <br> договоров</div>
      <div>новых <br> услуг</div>
      <div>сумма <br> договоров</div>
      <div>увеличение <br> услуг</div>
      <div>уменьшение <br> услуг</div>
      <div>изменение <br> суммы</div>
    </div>
  </Teleport>
  <div class="table stats-list">
    <div
      v-for="item in items"
      :key="item.date"
    >
      <div class="text-gray">
        {{ formatDateMode(item.date, mode) }}
      </div>
      <div>
        {{ item.requests_count || "" }}
      </div>
      <div>
        {{ item.new_contracts_count || "" }}
      </div>
      <div>
        {{ item.new_programs_count || "" }}
      </div>
      <div>
        {{ item.new_contracts_sum ? formatPrice(item.new_contracts_sum) : '' }}
      </div>
      <div>
        {{ item.programs_added_count || '' }}
      </div>
      <div>
        {{ item.programs_removed_count || '' }}
      </div>
      <div>
        {{ item.contracts_sum_change ? formatPrice(item.contracts_sum_change) : '' }}
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
      width: 100px;
      &:nth-child(1) {
        width: 200px;
      }
      &:nth-child(5) {
        width: 150px;
      }
      &:last-child {
        width: auto;
        flex: 1;
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
