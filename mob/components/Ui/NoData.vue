<script setup lang="ts">
import { mdiBookOpenBlankVariantOutline } from '@mdi/js'

const {
  icon = mdiBookOpenBlankVariantOutline,
} = defineProps<{
  icon?: string
}>()
</script>

<template>
  <div class="no-data">
    <div>
      <v-icon :icon="icon" />
      <slot>
        нет данных
      </slot>
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
  text-align: center;
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
