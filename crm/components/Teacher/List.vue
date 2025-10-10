<script setup lang="ts">
import type { TeacherListResource } from '.'
import { mdiWeb } from '@mdi/js'

const { items } = defineProps<{
  items: TeacherListResource[]
}>()
</script>

<template>
  <div class="table">
    <div
      v-for="teacher in items"
      :key="teacher.id"
    >
      <!-- <div style="width: 50px">
        {{ teacher.id }}
      </div> -->
      <div style="width: 150px">
        <NuxtLink :to="{ name: 'teachers-id', params: { id: teacher.id } }">
          {{ formatName(teacher, 'initials') }}
        </NuxtLink>
      </div>
      <div style="width: 200px">
        {{ TeacherStatusLabel[teacher.status] }}
      </div>
      <div style="width: 120px">
        {{ teacher.subjects.map(s => SubjectLabelShort[s]).join('+') }}
      </div>
      <div style="width: 50px">
        <v-icon :icon="mdiWeb" :class="teacher.is_published ? 'text-secondary' : 'opacity-2 text-gray'" />
      </div>
      <div>
        <TeethBar v-if="teacher.status === 'active'" :items="teacher.teeth" />
      </div>
    </div>
  </div>
</template>
