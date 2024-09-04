<template>
  <div class="loader">
    <span> загрузка... </span>
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
  span {
    color: rgb(var(--v-theme-gray));
    animation-name: loader;
    animation-duration: 2s;
    animation-timing-function: linear;
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
</style>
