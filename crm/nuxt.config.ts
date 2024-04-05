import vuetify, { transformAssetUrls } from "vite-plugin-vuetify"

export default defineNuxtConfig({
  // devtools: { enabled: true },
  // ssr: false,
  css: ["~/assets/scss/main.scss"],

  build: {
    transpile: ["vuetify"],
  },

  runtimeConfig: {
    public: {
      env: "",
      baseUrl: "",
      pusherAppKey: "",
    },
  },

  modules: [
    "@pinia/nuxt",
    (_options, nuxt) => {
      nuxt.hooks.hook("vite:extendConfig", (config) => {
        // @ts-expect-error
        config.plugins.push(
          vuetify({
            autoImport: true,
          }),
        )
      })
    },
  ],

  vite: {
    vue: {
      template: {
        transformAssetUrls,
      },
    },
  },

  imports: {
    dirs: ["store"],
  },

  sourcemap: {
    server: true,
  },
})
