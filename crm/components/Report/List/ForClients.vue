<script setup lang="ts">
const { items } = defineProps<{
  items: RealReport[]
}>()
</script>

<template>
  <div class="table">
    <div v-for="r in items" :id="`report-${r.id}`" :key="r.id">
      <div style="width: 170px">
        <UiPerson :item="r.teacher" />
      </div>
      <div style="width: 140px">
        {{ ProgramShortLabel[r.program] }}
      </div>
      <div style="width: 180px">
        прошло занятий: {{ r.lessons_count }}
      </div>
      <div style="width: 50px">
        <span v-if="r.grade" :class="`score score--${r.grade}`">
          {{ r.grade }}
        </span>
      </div>
      <div style="width: 100px; flex: initial" class="text-gray">
        {{ formatTextDate(r.created_at, true) }}
      </div>
      <div class="text-right">
        <RouterLink :to="{ name: 'reports-id', params: { id: r.id } }">
          читать отчёт
        </RouterLink>
      </div>
    </div>
  </div>
</template>
