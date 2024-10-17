import type { ThemeDefinition } from 'vuetify'
import { createVuetify } from 'vuetify'
import { aliases, mdi } from 'vuetify/iconsets/mdi-svg'
import {
  mdiCheckDecagram,
  mdiChevronDown,
  mdiClockOutline,
  mdiContentSaveCheckOutline,
  mdiDelete,
  mdiDotsHorizontal,
  mdiEyeArrowRightOutline,
  mdiEyeOutline,
  mdiInformationOutline,
  mdiMagnify,
  mdiPassport,
  mdiSend,
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
    eye: mdiEyeOutline,
    search: mdiMagnify,
    info: mdiInformationOutline,
    send: mdiSend,
    time: mdiClockOutline,
    pass: mdiPassport,
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
  bg: '#fafafa', // очень светлый серый
  border: '#e0e0e0', // серый бордеры
  placeholder: '#a7a7a7',
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
