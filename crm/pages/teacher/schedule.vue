<script setup lang="ts">
const { user } = useAuthStore()
</script>

<template>
  <LessonList :id="user?.id!" entity="teacher" />
</template>
