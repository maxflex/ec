<script setup lang="ts">
import type { TeacherContractData } from '.'

const { items, highlightIds } = defineProps<{
  items: TeacherContractData
  highlightIds?: number[]
}>()

// Простая проверка: нужно ли подсвечивать строку?
const isHighlighted = (groupId: number) => {
  return highlightIds?.includes(groupId)
}
</script>

<template>
  <table class="dialog-table teacher-contract__data-table">
    <thead>
      <tr>
        <th>группа</th>
        <th>занятий</th>
        <th>по стоимости</th>
      </tr>
    </thead>
    <tbody>
      <tr
      v-for="(item, i) in items"
      :key="`${item.group_id}-${item.price}-${item.lessons}`"
      :class="{ 'teacher-contract__group-problems': isHighlighted(item.group_id) }"
      class="padding">
        <td :class="{ 'teacher-contract__same-group': i > 0 && items[i - 1].group_id === item.group_id }">
          <span> ГР-{{ item.group_id }} </span>
        </td>
        <td>
          {{ item.lessons }}
        </td>
        <td>
          {{ formatPrice(item.price) }} руб.
        </td>
      </tr>
      <tr>
        <td></td>
        <td>
          {{ items.reduce((carry, x) => carry + x.lessons, 0) }}
        </td>
        <td>
          {{ formatPrice(items.reduce((carry, x) => carry + (x.lessons * x.price), 0)) }} руб.
        </td>
      </tr>
    </tbody>
  </table>
</template>

<style lang="scss">
.teacher-contract {
  &__same-group {
    position: relative;
    span {
      display: none;
    }
    &:before {
      content: '';
      position: absolute;
      width: 100%;
      height: 1px;
      left: 0;
      top: -1px;
      background: white;
    }
  }

  &__group-problems {
    td {
      background: #fde9e6;
      &.teacher-contract__same-group:before {
        background: #fde9e6 !important;
      }
    }
  }
}
</style>
