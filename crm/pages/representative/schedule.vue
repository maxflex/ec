<script setup lang="ts">
const { user } = useAuthStore()
</script>

<template>
  <Schedule :client-id="user?.client_id!" />
</template>
