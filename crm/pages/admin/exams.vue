<script setup lang="ts">
interface ExamResource {
  exam: Exam
  programs: Program[]
}

const { items } = useIndex<ExamResource>(`exams`)
</script>

<template>
  <div class="table table--hover">
    <div v-for="item in items" :key="item.exam">
      <div style="width: 300px">
        {{ ExamLabel[item.exam] }}
      </div>
      <div>
        {{ item.programs.map(e => ProgramShortLabel[e]).join(', ') }}
      </div>
    </div>
  </div>
</template>
