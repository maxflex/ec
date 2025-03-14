import vuetify, { transformAssetUrls } from 'vite-plugin-vuetify'

export default defineNuxtConfig({
  devtools: { enabled: false },
  // ssr: false,
  css: ['~/assets/scss/index.scss'],

  build: {
    transpile: ['vuetify'],
  },

  runtimeConfig: {
    public: {
      env: '',
      baseUrl: '',
    },
    redisHost: '',
  },

  modules: [
    (_options, nuxt) => {
      nuxt.hooks.hook('vite:extendConfig', (config) => {
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
    css: {
      preprocessorOptions: {
        scss: {
          api: 'modern-compiler',
        },
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

  app: {
    head: {
      link: [
        { rel: 'icon', type: 'image/svg+xml', href: '/img/favicon.svg' },
      ],
      title: 'Личный кабинет V3',
    },
  },

  compatibilityDate: '2024-10-31',
})
