<script setup lang="ts">
const { user } = useAuthStore()
</script>

<template>
  <h1 class="page-title">
    Расписание
  </h1>
  <Schedule :client-id="user?.id!" />
</template>
