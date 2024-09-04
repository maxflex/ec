<script setup lang="ts">
const { user } = useAuthStore()
</script>

<template>
  <Balance :teacher-id="user?.id!" />
</template>
