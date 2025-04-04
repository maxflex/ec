<script setup lang="ts">
import { mdiMonitor } from '@mdi/js'
</script>

<template>
  <UiNoData
    :icon="mdiMonitor"
    class="text-black page-only-desktop"
  >
    Личный кабинет доступен<br />
    только на компьютере
  </UiNoData>
</template>

<style lang="scss">
.page-only-desktop {
  & > div {
    font-size: 20px;
    opacity: 0.8 !important;
  }
  .v-icon {
    font-size: 54px !important;
    opacity: 0.8 !important;
  }
}
</style>
