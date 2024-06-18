<template>
  <v-app class="login-layout">
    <div class="video-background">
      <video
        id="background-video"
        autoplay
        loop
        muted
      >
        <source
          src="/loop.mp4"
          type="video/mp4"
        >
        Your browser does not support the video tag.
      </video>
    </div>
    <div>
      <ClientOnly>
        <slot />
      </ClientOnly>
    </div>
  </v-app>
</template>

<style lang="scss">
.login-layout > div > div:last-child {
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
.video-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    overflow: hidden;
    z-index: 0; /* Ensure the video stays behind other content */
}

#background-video {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 103vw;
    height: 103vh;
    object-fit: cover; /* Ensures the video covers the entire viewport */
    transform: translate(-50%, -50%);
    z-index: -1;
    filter: blur(3px); /* Apply blur effect */
}
</style>
