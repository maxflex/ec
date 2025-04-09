<script setup lang="ts">
const teacherId = 23
const teacher = ref<TeacherResource>()

async function loadData() {
  const { data } = await useHttp<TeacherResource>(`teachers/${teacherId}`)
  teacher.value = data.value!
}

nextTick(loadData)
</script>

<template>
  <TeacherStatsTab v-if="teacher" :teacher="teacher" />
</template>
