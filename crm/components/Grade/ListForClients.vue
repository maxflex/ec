<script setup lang="ts">
const { items } = defineProps<{
  items: QuartersGradesResource[]
}>()
</script>

<template>
  <v-table hover class="grades-for-clients">
    <thead>
      <tr>
        <th />
        <th v-for="(q, key) in QuarterLabel" :key="key" class="text-center">
          {{ q }}
        </th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="g in items" :key="g.id">
        <td width="200">
          {{ ProgramShortLabel[g.program] }}
        </td>
        <td v-for="(q, key) in g.quarters" :key="key" width="160" class="text-center">
          <span v-if="q.grade" :class="`text-score text-score--${q.grade.grade}`">
            {{ q.grade.grade }}
          </span>
        </td>
      </tr>
    </tbody>
  </v-table>
</template>

<style lang="scss">
.grades-for-clients {
  tr {
    td,
    th {
      &:nth-child(6) {
        border-left: 1px solid rgb(var(--v-theme-border));
      }
    }
  }
}
</style>
