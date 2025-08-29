<script setup lang="ts">
const { user } = useAuthStore()
</script>

<template>
  <UiPageTitle>
    Расписание
  </UiPageTitle>
  <Schedule :client-id="user?.id!" />
</template>
