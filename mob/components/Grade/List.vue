<script setup lang="ts">
const { items } = defineProps<{
  items: QuartersGradesResource[]
}>()
</script>

<template>
  <div class="table grade-list">
    <!-- <div class="grade-list__row">
      <div>
        программа
      </div>
      <div class="grade-list__grades">
        <div v-for="(label, id) in QuarterShortLabel" :key="id">
          {{ label }}
        </div>
      </div>
    </div> -->
    <div v-for="g in items" :key="g.id" class="grade-list__row">
      <div>
        {{ ProgramShortLabel[g.program] }}
      </div>
      <div class="grade-list__grades">
        <div v-for="(q, key) in g.quarters" :key="key">
          <span v-if="q.grade" :class="`text-score text-score--${q.grade.grade}`">
            {{ q.grade.grade }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.grade-list {
  &__grades {
    display: flex;
    // gap: 24px;
    & > div {
      flex: 1;
      text-align: center;
    }
  }
  &__row {
    padding-right: 0 !important;
    & > div {
      &:first-child {
        width: 100px;
      }
    }
  }
}
</style>
