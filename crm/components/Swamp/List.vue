<script setup lang="ts">
import type { SwampListResource } from '.'

const { items } = defineProps<{
  items: SwampListResource[]
}>()
</script>

<template>
  <div class="table table--padding swamp-list">
    <div v-for="item in items" :key="item.id" :class="{ changed: item.changes }">
      <!-- добавлен в группу по программе -->
      <SwampItemAttached v-if="item.group" :item="item" />

      <!-- не добавлен в группу программе -->
      <SwampItemUnattached v-else :item="item" />
    </div>
  </div>
</template>

<style lang="scss">
.swamp-list {
  & > div {
    align-items: flex-start !important;

    & > div {
      // group
      &:first-child {
        width: 90px;
      }
      // teacher
      &:nth-child(2) {
        width: 180px;
      }
      // program
      &:nth-child(3) {
        width: 110px;
      }
      // first lesson date
      &:nth-child(4) {
        width: 80px;
      }
      // lessons -> counts
      &:nth-child(5) {
        width: 70px;
      }
      // ученики кол-во
      &:nth-child(6) {
        width: 90px;
      }

      // расписание
      &:nth-child(7) {
        width: 180px;
      }

      // кабинет
      &:nth-child(8) {
        width: 80px;
      }

      &:last-child {
        width: initial;
        flex: 1;
      }
    }
  }
}
</style>
