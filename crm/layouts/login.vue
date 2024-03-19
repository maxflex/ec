<template>
  <v-app class="login-layout">
    <div>
      <slot />
    </div>
  </v-app>
</template>

<style lang="scss">
.login-layout > div > div {
  // background: linear-gradient(to bottom, #f1e4a5 0%, rgba(255, 196, 35, 0.15));
  // background-image: url("/img/sky-blurred.jpg");
  // background-size: cover;
  background: #fff7e2;
  height: 100vh;
  width: 100vw;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
