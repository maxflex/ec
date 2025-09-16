<script setup lang="ts">
const { user } = useAuthStore()
</script>

<template>
  <Schedule :client-id="user?.id!" show-holidays />
</template>
