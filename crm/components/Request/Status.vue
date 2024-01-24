<script setup lang="ts">
// import type { RequestStatus } from "@/types"
const { status } = defineProps<{ status: RequestStatus }>()
</script>

<template>
  <div class="request-status">
    {{ status }}
  </div>
</template>
