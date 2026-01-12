<script setup lang="ts">
interface ExamResource {
  exam: Exam
  programs: Program[]
}

const { items } = useIndex<ExamResource>(`exams`)
</script>

<template>
  <Table hoverable>
    <TableRow v-for="item in items" :key="item.exam">
      <TableCol :width="300">
        {{ ExamLabel[item.exam] }}
      </TableCol>
      <TableCol>
        {{ item.programs.map(e => ProgramShortLabel[e]).join(', ') }}
      </TableCol>
    </TableRow>
  </Table>
</template>
