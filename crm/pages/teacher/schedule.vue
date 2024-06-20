<script setup lang="ts">
const { user } = useAuthStore()
</script>

<template>
  <ScheduleList :id="user?.id!" entity="teacher" />
</template>
