<script setup lang="ts">
const { user } = useAuthStore()
</script>

<template>
  <Schedule :teacher-id="user?.id!" />
</template>
