import vuetify, { transformAssetUrls } from 'vite-plugin-vuetify'

export default defineNuxtConfig({
  // devtools: { enabled: true },
  // ssr: false,
  css: ['~/assets/scss/index.scss'],

  build: {
    transpile: ['vuetify'],
  },

  runtimeConfig: {
    public: {
      env: '',
      baseUrl: '',
      pusherAppKey: '',
    },
    redisHost: '',
  },

  modules: [
    (_options, nuxt) => {
      nuxt.hooks.hook('vite:extendConfig', (config) => {
        // @ts-expect-error
        config.plugins?.push(vuetify({ autoImport: true }))
      })
    },
    '@pinia/nuxt',
    '@nuxt/eslint',
  ],

  eslint: {
    config: {
      stylistic: true,
    },
  },

  vite: {
    vue: {
      template: {
        transformAssetUrls,
      },
    },
  },

  imports: {
    dirs: ['store'],
  },

  sourcemap: {
    server: true,
  },

  routeRules: {
    '/api/**': {
      cors: true,
    },
  },
})
