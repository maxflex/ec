<script setup lang="ts">
const { status } = defineProps<{
  status: ReportStatus
}>()
</script>

<template>
  <span :class="{ 'text-error font-weight-medium': status === 'refused' }">
    {{ ReportStatusLabel[status] }}
  </span>
</template>
