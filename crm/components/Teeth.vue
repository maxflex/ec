<script setup lang="ts">
const { items, current } = defineProps<{
  items: Teeth
  current?: Teeth
}>()

function isCurrent(weekday: Weekday, tooth: Tooth): boolean {
  if (current === undefined || !(weekday in current)) {
    return false
  }
  const { left, width } = tooth
  for (const t of current[weekday]) {
    if (t.left === left && t.width === width) {
      return true
    }
  }
  return false
}
</script>

<template>
  <div class="teeth">
    <div
      v-for="(label, weekday) in WeekdayLabel"
      :key="weekday"
      :class="`teeth__day teeth__day--${weekday}`"
    >
      <div
        v-for="(tooth, index) in items[weekday]"
        :key="index"
        class="teeth__tooth"
        :class="{
          'teeth__tooth--current': isCurrent(weekday, tooth),
        }"
        :style="{
          left: `${tooth.left}%`,
          width: `${tooth.width}%`,
        }"
      >
        <v-tooltip location="bottom">
          <template #activator="{ props }">
            <div v-bind="props" />
          </template>
          {{ label.toUpperCase() }} {{ formatTime(tooth.time) }} – {{ formatTime(tooth.time_end) }}
        </v-tooltip>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.teeth {
  $width: 50px;
  $height: 15px;
  $gap: 10px;
  display: flex;
  align-items: center;
  gap: $gap;
  &__day {
    width: $width;
    height: $height;
    display: inline-block;
    background: rgb(var(--v-theme-bg2));
    position: relative;
    border-radius: 2px;
    overflow: hidden;
    &--5 {
      // margin-left: $gap;
    }
  }
  &__tooth {
    background: rgb(var(--v-theme-secondary));
    opacity: 0.8;
    height: 100%;
    position: absolute;
    top: 0;
    &:hover {
      opacity: 1;
    }
    // tooltip
    & > div {
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
    }
    &--current {
      background: rgb(var(--v-theme-success));
    }
  }
}
</style>
