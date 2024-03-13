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
  background: #fff7e2;

  // background-image: url("/img/sky-blurred.jpg");
  // background-size: cover;
  height: 100vh;
  width: 100vw;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  form {
    // background: rgba(white, 0.4);
    background: white;
    border-radius: 20px;
    width: 400px;
    padding: 30px;
    // text-align: center;
    // box-shadow: 0 11px 34px 0 rgb(0 0 0 / 20%);
    border: 2px solid rgba(255, 196, 35, 0.5);
    img {
      width: 80px;
      margin: 0 auto 50px;
      // filter: grayscale(100%);
      // filter: sepia(60%);
    }
    & > .v-text-field {
      margin-bottom: 20px;
    }
    button[type="submit"] {
      margin-top: 60px;
    }
  }
}
</style>
