<script setup lang="ts">
const { items } = defineProps<{
  items: Teeth
}>()
</script>

<template>
  <div class="teeth-as-text">
    <div v-for="(teeth, weekday) in items" :key="weekday">
      <span>
        {{ WeekdayLabel[weekday] }}
      </span>
      <div class="teeth-as-text__times">
        <div v-for="(t, index) in teeth.sort((a, b) => a.left - b.left)" :key="index">
          {{ formatTime(t.time) }} – {{ formatTime(t.time_end) }}
        </div>
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
      &:not(:last-child):after {
        content: ',';
      }
    }
  }
}
</style>
