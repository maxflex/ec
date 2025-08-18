<script setup lang="ts">
const { items, current } = defineProps<{
  items: Teeth
  current?: Teeth
}>()

function isSameTooth(a: Tooth, b: Tooth): boolean {
  return a.left === b.left && a.width === b.width
}

function isCurrentTooth(weekday: Weekday, tooth: Tooth): boolean {
  return current?.[weekday]?.some(t => isSameTooth(t, tooth)) ?? false
}

// items + current (if exists)
const allItems = computed(() => {
  const result: Teeth = {}

  for (const weekday of Object.keys(WeekdayLabel) as unknown as Weekday[]) {
    const itemTeeth: Tooth[] = items[weekday] ?? []
    const currentTeeth: Tooth[] = current?.[weekday] ?? []

    const merged: Tooth[] = [...itemTeeth]

    for (const ct of currentTeeth) {
      if (!merged.some(t => isSameTooth(t, ct))) {
        merged.push(ct)
      }
    }

    result[weekday] = merged
  }

  return result
})
</script>

<template>
  <div class="teeth">
    <div
      v-for="(label, weekday) in WeekdayLabel"
      :key="weekday"
      :class="`teeth__day teeth__day--${weekday}`"
    >
      <div
        v-for="(tooth, index) in allItems[weekday]"
        :key="index"
        class="teeth__tooth"
        :class="{
          'teeth__tooth--current': isCurrentTooth(weekday, tooth),
          'teeth__tooth--is-past': tooth.is_past,
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
  $width: 70px;
  $height: 15px;
  $gap: 10px;
  display: flex;
  align-items: center;
  gap: $gap;
  &__day {
    width: $width;
    height: $height;
    display: inline-block;
    background: rgb(var(--v-theme-border));
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
      background: rgb(var(--v-theme-deepOrange));
    }

    &--is-past {
      background: #adc9e1;
      // current + is-past
      &.teeth__tooth--current {
        background: #f6c6a4 !important;
      }
    }
  }
}
</style>
