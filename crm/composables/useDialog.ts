type DialogWidth = 'small' | 'default' | 'medium' | 'large'

const dialogWidths: { [key in DialogWidth]: number } = {
  small: 406,
  default: 500,
  medium: 800,
  large: 1000,
}

export default function (w: DialogWidth) {
  const dialog = ref(false)
  const width = dialogWidths[w]
  const transition = ref('dialog-fade-transition')
  watch(dialog, (val) => {
    const el = document.documentElement.querySelector(
      '.v-dialog.v-overlay--active > .dialog',
    )
    if (el === null) {
      transition.value = 'dialog-fade-transition'
    }
    else {
      transition.value = 'dialog-second-transition'
      // @ts-expect-error
      el.style.right = val ? `${width * 0.5}px` : null
    }
  })

  return { dialog, width, transition }
}
