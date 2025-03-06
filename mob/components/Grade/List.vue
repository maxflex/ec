<script setup lang="ts">
const { items } = defineProps<{
  items: QuartersGradesResource[]
}>()
</script>

<template>
  <div class="table grade-list">
    <div v-for="g in items" :key="g.id">
      <div style="width: 120px">
        {{ ProgramShortLabel[g.program] }}
      </div>
      <div class="grade-list__grades">
        <div v-for="(q, key) in g.quarters" :key="key" class="text-center">
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
    gap: 24px;
  }
}
</style>
