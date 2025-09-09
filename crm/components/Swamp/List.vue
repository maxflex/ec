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
    flex-direction: column;
    align-items: flex-start !important;
    padding: 0 !important;
    gap: 0 !important;

    & > div {
      display: flex;
      width: 100%;
      padding-top: 16px;
      padding-bottom: 16px;

      & > div {
        width: 100px;

        &:first-child {
          width: 130px;
          padding-left: 20px;
        }
        // teacher
        &:nth-child(2) {
          width: 200px;
        }
        // program
        &:nth-child(3) {
          width: 130px;
        }
        // first lesson date
        &:nth-child(4) {
          width: 100px;
        }
        // расписание
        &:nth-child(7) {
          width: 200px;
        }

        &:last-child {
          width: initial;
          flex: 1;
          padding-right: 20px;
        }
      }
    }
  }
}
</style>
