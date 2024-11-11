<script setup lang="ts">
import { mdiExclamationThick } from '@mdi/js'

const { items } = defineProps<{
  items: QuartersGradesResource[]
}>()
const router = useRouter()
</script>

<template>
  <v-table hover class="grades-table">
    <thead>
      <tr>
        <th />
        <th />
        <th v-for="(q, key) in QuarterLabel" :key="key" class="text-center">
          {{ q }}
        </th>
      </tr>
    </thead>
    <tbody>
      <tr
        v-for="g in items"
        :key="g.id"
        class="cursor-pointer"
        @click="() => router.push({ name: 'grades-id', params: { id: g.id } })"
      >
        <td>
          <NuxtLink
            :to="{ name: 'clients-id', params: { id: g.client.id } }"
            @click.stop
          >
            {{ formatName(g.client) }}
          </NuxtLink>
        </td>
        <td width="200">
          {{ ProgramShortLabel[g.program] }}
        </td>
        <td v-for="(q, key) in g.quarters" :key="key" width="160" class="text-center">
          <span v-if="q.grade" :class="`score score--${q.grade.grade}`">
            {{ q.grade.grade }}
          </span>
          <v-icon v-else-if="q.is_grade_needed" :icon="mdiExclamationThick" color="error" />
        </td>
      </tr>
    </tbody>
  </v-table>
</template>

<style lang="scss">
.grades-table {
  tr {
    td,
    th {
      &:nth-child(7) {
        border-left: 1px solid rgb(var(--v-theme-border));
      }
    }
  }
}
</style>
