import { createVuetify } from 'vuetify'
import type { ThemeDefinition } from 'vuetify'
import { aliases, mdi } from 'vuetify/iconsets/mdi-svg'
import {
  mdiDotsHorizontal,
  mdiContentSaveCheckOutline,
  mdiChevronDown,
  mdiCheckDecagram,
  mdiDelete,
  mdiEyeArrowRightOutline,
  mdiTuneVertical,
} from '@mdi/js'

const icons = {
  defaultSet: 'mdi',
  aliases: {
    ...aliases,
    more: mdiDotsHorizontal,
    save: mdiContentSaveCheckOutline,
    dropdown: mdiChevronDown,
    verified: mdiCheckDecagram,
    delete: mdiDelete,
    preview: mdiEyeArrowRightOutline,
    filters: mdiTuneVertical,
  },
  sets: {
    mdi,
  },
}

const colors = {
  primary: '#ffd572', // оранжевый приглушённый
  secondary: '#337ab7', // синий
  orange: '#ffc423', // оранжевый
  deepOrange: '#fe8a1e',
  accent: '#4346d5',
  success: '#00A272',
  grey: '#949db1',
  gray: '#949db1',
  error: '#eb4432',
  red: '#eb4432',
  bg: '#fafafa',
}

const myTheme: ThemeDefinition = {
  dark: false,
  colors,
}

export default defineNuxtPlugin((nuxtApp) => {
  const vuetify = createVuetify({
    icons,
    defaults: {
      VTextField: {
        variant: 'outlined',
        hideDetails: true,
      },
      VTextarea: {
        variant: 'outlined',
        hideDetails: true,
      },
      VSelect: {
        variant: 'outlined',
        hideDetails: true,
      },
      VBtn: {
        variant: 'flat',
        size: 'large',
        rounded: 'xl',
        // ripple: false,
      },
      VDialog: {
        fullscreen: true,
        transition: 'dialog-fade-transition',
        // transition: 'dialog-right-transition',
        // transition: 'scroll-x-reverse-transition',
        contentClass: 'dialog',
      },
      VCheckbox: {
        density: 'comfortable',
        hideDetails: true,
        // color: colors.deepOrange,
      },
    },
    theme: {
      defaultTheme: 'myTheme',
      themes: {
        myTheme,
      },
    },
  })
  nuxtApp.vueApp.use(vuetify)
})
