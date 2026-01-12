<script setup lang="ts">
import type { TeacherListResource } from '.'
import { mdiWeb } from '@mdi/js'

const { items } = defineProps<{
  items: TeacherListResource[]
}>()
</script>

<template>
  <Table>
    <TableRow
      v-for="teacher in items"
      :key="teacher.id"
    >
      <!-- <div style="width: 50px">
        {{ teacher.id }}
      </div> -->
      <TableCol :width="150">
        <NuxtLink :to="{ name: 'teachers-id', params: { id: teacher.id } }">
          {{ formatName(teacher, 'initials') }}
        </NuxtLink>
      </TableCol>
      <TableCol :width="200">
        {{ TeacherStatusLabel[teacher.status] }}
      </TableCol>
      <TableCol :width="120">
        {{ teacher.subjects.map(s => SubjectLabelShort[s]).join('+') }}
      </TableCol>
      <TableCol :width="50">
        <v-icon :icon="mdiWeb" :class="teacher.is_published ? 'text-secondary' : 'opacity-2 text-gray'" />
      </TableCol>
      <TableCol>
        <TeethBar v-if="teacher.status === 'active'" :items="teacher.teeth" />
      </TableCol>
    </TableRow>
  </Table>
</template>
