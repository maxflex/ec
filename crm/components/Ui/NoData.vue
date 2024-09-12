<script setup lang="ts">
import { mdiBookOpenBlankVariantOutline } from '@mdi/js'

const { label } = withDefaults(
  defineProps<{
    label?: string
    icon?: string
  }>(),
  {
    label: 'нет данных',
    icon: mdiBookOpenBlankVariantOutline,
  },
)
</script>

<template>
  <div class="no-data">
    <div>
      <v-icon :icon="icon" />
      <div>{{ label }}</div>
    </div>
  </div>
</template>

<style lang="scss">
.no-data {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  width: 100%;
  flex: 1;
  color: rgb(var(--v-theme-gray));
  position: absolute;
  & > div {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 10px;
    opacity: 0.5;
  }
  .v-icon {
    font-size: 50px;
    opacity: 0.5;
  }
}
</style>
