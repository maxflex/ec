<template>
  <div class="loaderr">
    <span> загрузка... </span>
  </div>
</template>

<style lang="scss">
.loaderr {
  background: white;
  height: 100%;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  span {
    color: rgb(var(--v-theme-gray));
    animation-name: loaderr;
    animation-duration: 2s;
    animation-timing-function: linear;
  }
}
@keyframes loaderr {
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
