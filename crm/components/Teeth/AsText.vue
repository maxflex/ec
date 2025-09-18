<script setup lang="ts">
const { items, oneLine } = defineProps<{
  items: Teeth
  oneLine?: boolean
}>()

type Result = Array<{
  weekday: Weekday
  teeth: Teeth
  all_past: boolean
}>

function getForWeekday(teeth: Teeth, weekday: Weekday): Teeth {
  return teeth.filter(e => e.weekday === weekday)
}

const itemsByWeekday = computed<Result>(() => {
  // const result: Partial<Record<Weekday, Teeth>> = {}
  const result: Result = []

  for (const weekday of [0, 1, 2, 3, 4, 5, 6, 7] as Weekday[]) {
    const teeth = getForWeekday(items, weekday)
    if (teeth.length) {
      result.push({
        weekday,
        teeth,
        all_past: teeth.some(e => !e.is_past) === false,
      })
    }
  }

  return result
})
</script>

<template>
  <div class="teeth-as-text" :class="{ 'teeth-as-text--one-line': oneLine }">
    <div
      v-for="item in itemsByWeekday"
      :key="item.weekday"
      :class="{
        'teeth-as-text--is-past': item.all_past,
      }"
    >
      <span>
        {{ WeekdayLabel[item.weekday] }}
      </span>
      <div class="teeth-as-text__times">
        <span
          v-for="(t, index) in item.teeth.sort((a, b) => a.left - b.left)"
          :key="index"
          :class="{ 'teeth-as-text--is-past': t.is_past }"
        >
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
          // color: black;
        }
      }
    }
  }

  &--is-past {
    color: rgb(var(--v-theme-gray));
  }
}
</style>
