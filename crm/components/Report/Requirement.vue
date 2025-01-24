<script setup lang="ts">
const { requirement } = defineProps<{
  requirement: ReportRequirement
}>()
</script>

<template>
  <span :class="requirement === 'required' ? 'text-error' : 'text-gray'">
    {{ ReportRequirementLabel[requirement] }}
  </span>
</template>
