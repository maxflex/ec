<script setup lang="ts">
const { offset, fixed } = defineProps<{
  fixed?: boolean
  offset?: number
}>()

const style = offset
  ? {
      height: `calc(100% - ${offset}px)`,
      top: `${offset}px`,
    }
  : {}
</script>

<template>
  <div class="loader" :class="{ 'loader--fixed': fixed }" :style="style">
    <span> загружаю... </span>
  </div>
</template>

<style lang="scss">
.loader {
  background: white;
  height: 100%;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  position: absolute;
  z-index: 1;
  &--fixed {
    position: fixed;
    top: 0;
    left: 0;
  }
  span {
    font-size: 30px;
    position: relative;
    color: transparent; /* Makes the text invisible initially */
    background: linear-gradient(120deg, rgba(0, 0, 0, 0.1) 25%, rgba(var(--v-theme-gray)) 50%, rgba(0, 0, 0, 0.1) 75%);
    background-size: 200% 200%;
    -webkit-background-clip: text;
    background-clip: text;
    animation:
      loader 2s linear,
      glide 3s infinite linear;
  }
}
@keyframes loader {
  from {
    opacity: 0;
  }
  80% {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes glide {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 100%;
  }
}
</style>
