<script setup lang="ts">
const { items, oneLine } = defineProps<{
  items: Teeth
  oneLine?: boolean
}>()
</script>

<template>
  <div class="teeth-as-text" :class="{ 'teeth-as-text--one-line': oneLine }">
    <div v-for="(teeth, weekday) in items" :key="weekday">
      <span>
        {{ WeekdayLabel[weekday] }}
      </span>
      <div class="teeth-as-text__times">
        <span v-for="(t, index) in teeth.sort((a, b) => a.left - b.left)" :key="index">
          {{ formatTime(t.time) }} – {{ formatTime(t.time_end) }}
        </span>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.teeth-as-text {
  text-transform: uppercase;
  & > div {
    display: flex;
    & > span {
      display: inline-block;
      width: 30px;
      //color: rgb(var(--v-theme-gray));
    }
  }
  &__times {
    display: flex;
    flex-direction: column;
    & > span {
      margin-right: 4px;
    }
  }

  &--one-line {
    display: flex;
    & > div {
      white-space: nowrap;
      margin-right: 40px;
      & > span {
        width: auto !important;
        margin-right: 4px;
      }
    }
    .teeth-as-text__times {
      flex-direction: row !important;

      & > span {
        margin-right: 4px;
        &:not(:last-child):after {
          content: ', ';
        }
      }
    }
  }
}
</style>
