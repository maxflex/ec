<script setup lang="ts">
const { icon } = defineProps<{ icon?: string }>()
</script>

<template>
  <div class="no-data">
    <div>
      <v-icon v-if="icon" :icon="icon" />
      <div>
        <slot>
          нет данных
        </slot>
      </div>
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
    gap: 0;
    opacity: 0.5;
    font-size: 30px;
    text-align: center;
  }
  .v-icon {
    font-size: 50px;
    opacity: 0.5;
  }
}
</style>
