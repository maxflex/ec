<script setup lang="ts">
import { mdiArrowRightThin } from '@mdi/js'

const { item } = defineProps<{
  item: {
    lessons_conducted: number
    total_lessons: number
    status: CvpStatus
  }
}>()
</script>

<template>
  <span :class="`cvp-status cvp-status--${item.status}`">
    {{ item.lessons_conducted }}
    <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
    {{ item.total_lessons }}
  </span>
</template>

<style lang="scss">
.cvp-status {
  &--active {
    color: rgb(var(--v-theme-deepOrange));
  }

  &--finished {
    color: rgb(var(--v-theme-success));
  }

  &--exceeded {
    color: rgb(var(--v-theme-error));
  }
}
</style>
