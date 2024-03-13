import { createVuetify } from "vuetify"
import { aliases, mdi } from "vuetify/iconsets/mdi-svg"
import {
  mdiDotsHorizontal,
  mdiContentSaveCheckOutline,
  mdiChevronDown,
  mdiCheckDecagram,
  mdiDelete,
  mdiChevronRight,
} from "@mdi/js"
import type { ThemeDefinition } from "vuetify"

const colors = {
  primary: "#ffd572", // оранжевый приглушённый
  secondary: "#337ab7", // синий
  orange: "#ffc423", // оранжевый
  deepOrange: "#fe8a1e",
  accent: "#4346d5",
  success: "#00A272",
  grey: "#949db1",
  gray: "#949db1",
  error: "#ef1e25",
  red: "#ef1e25",
  bg: "#fafafa",
}

const myTheme: ThemeDefinition = {
  dark: false,
  colors,
}

export default defineNuxtPlugin((nuxtApp) => {
  const vuetify = createVuetify({
    icons: {
      defaultSet: "mdi",
      aliases: {
        ...aliases,
        more: mdiDotsHorizontal,
        save: mdiContentSaveCheckOutline,
        dropdown: mdiChevronDown,
        verified: mdiCheckDecagram,
        delete: mdiDelete,
        right: mdiChevronRight,
      },
      sets: {
        mdi,
      },
    },
    defaults: {
      VTextField: {
        variant: "outlined",
        hideDetails: true,
      },
      VSelect: {
        variant: "outlined",
        hideDetails: true,
      },
      VBtn: {
        variant: "flat",
        size: "large",
        rounded: "xl",
        // ripple: false,
      },
      VDialog: {
        fullscreen: true,
        transition: "dialog-right-transition",
        contentClass: "dialog",
      },
      VCheckbox: {
        density: "comfortable",
        hideDetails: true,
        // color: colors.deepOrange,
      },
    },
    theme: {
      defaultTheme: "myTheme",
      themes: {
        myTheme,
      },
    },
  })
  nuxtApp.vueApp.use(vuetify)
})
