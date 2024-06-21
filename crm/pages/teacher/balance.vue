<script setup lang="ts">
const { user } = useAuthStore()
</script>

<template>
  <BalanceList :id="user?.id!" entity="teacher" />
</template>
