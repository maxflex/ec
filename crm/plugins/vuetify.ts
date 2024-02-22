import { createVuetify } from "vuetify"
import { aliases, mdi } from "vuetify/iconsets/mdi-svg"
import {
  mdiDotsHorizontal,
  mdiCheckUnderline,
  mdiChevronDown,
  mdiCheckDecagram,
  mdiDelete,
} from "@mdi/js"
import type { ThemeDefinition } from "vuetify"

const colors = {
  primary: "#337ab7",
  secondary: "#ffd572", // оранжевый приглушённый
  orange: "#ffc423", // оранжевый
  deepOrange: "#fe8a1e",
  accent: "#4346d5",
  success: "#00A272",
  grey: "#949db1",
  gray: "#949db1",
  error: "#ef1e25",
  red: "#ef1e25",
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
        save: mdiCheckUnderline,
        dropdown: mdiChevronDown,
        verified: mdiCheckDecagram,
        delete: mdiDelete,
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

// colors: {
//   background: "#ffffff",
//   surface: "#ffffff",
//   primary: "#f1e4a5",
//   accent: "#f47fcc",
//   secondary: "#008ecb",
//   error: "#ff7134",
//   info: "#2196f3",
//   success: "#4caf50",
//   warning: "#ff4600",
//   orange: "#ff7134",
//   grey: "#9e9e9e",
//   "grey-light": "#b6b6b6",
//   "on-surface": "#414141",
// },

// VSelect: {
//   variant: "solo",
//   density: "comfortable",
//   hideDetails: true,
// },
// VTextField: {
//   variant: "outlined",
//   hideDetails: true,
// },
// VTextarea: {
//   variant: "outlined",
//   hideDetails: true,
// },
// VCheckbox: {
//   falseIcon: "jo-basic-circle",
//   trueIcon: "jo-basic-circle-checked",
//   hideDetails: true,
//   ripple: false,
//   density: "dense",
// },
