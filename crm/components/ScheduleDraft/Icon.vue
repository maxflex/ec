<script setup lang="ts">
import { mdiFileDocumentEditOutline } from '@mdi/js'
</script>

<template>
  <div class="d-flex align-center text-gray">
    <v-icon :icon="mdiFileDocumentEditOutline" :size="20" style="left: -4px; position: relative;" />
    <slot />
  </div>
</template>
