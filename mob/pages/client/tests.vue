<script setup lang="ts">
import { mdiMonitor } from '@mdi/js'
</script>

<template>
  <UiPageTitle>
    Тесты
  </UiPageTitle>
  <UiNoData :icon="mdiMonitor" style="top: -10vh">
    Этот раздел доступен<br />
    только на компьютере
  </UiNoData>
</template>
