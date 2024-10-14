<template>
  <v-app class="login-layout">
    <div class="video-background">
      <video
        id="background-video"
        autoplay
        loop
        muted
        poster="https://cdn.ege-centr.ru/cdn/videos/first-frame.webp"
      >
        <source
          src="https://cdn.ege-centr.ru/cdn/videos/loop.mp4"
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
  background: linear-gradient(45deg, #5a2d22, #c7784f);
  height: 100vh;
  width: 100vw;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  // &:after {
  //   content: '';
  //   background: rgba(black, 0.5);
  //   left: 0;
  //   top: 0;
  //   position: absolute;
  //   width: 100%;
  //   height: 100%;
  // }
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
  width: 100vw;
  height: 100vh;
  object-fit: cover; /* Ensures the video covers the entire viewport */
  transform: translate(-50%, -50%);
  z-index: -1;
  // filter: blur(3px); /* Apply blur effect */
}
</style>
