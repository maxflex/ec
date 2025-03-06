<script setup lang="ts">
const { items } = defineProps<{
  items: RealReport[]
}>()
</script>

<template>
  <div class="table table--padding">
    <RouterLink v-for="r in items" :id="`report-${r.id}`" :key="r.id" :to="{ name: 'reports-id', params: { id: r.id } }">
      <div style="flex: 1">
        <div>
          <UiPerson :item="r.teacher" />
        </div>
        <div>
          {{ ProgramShortLabel[r.program] }}
        </div>
      </div>
      <div style="width: 90px">
        <div>
          занятий: {{ r.lessons_count }}
        </div>
        <div class="text-gray">
          {{ formatDate(r.to_check_at) }}
        </div>
      </div>
      <div style="width: 20px; flex: initial" class="text-right">
        <span v-if="r.grade" :class="`text-score text-score--${r.grade}`">
          {{ r.grade }}
        </span>
      </div>
    </RouterLink>
  </div>
</template>
