type DialogWidth = 'default' | 'medium' | 'large' | 'x-large'

const dialogWidths: { [key in DialogWidth]: number } = {
  'default': 500,
  'medium': 800,
  'large': 1000,
  'x-large': 1300,
}

export default function (w: DialogWidth) {
  const dialog = ref(false)
  const width = dialogWidths[w]
  watch(dialog, (val) => {
    const el = document.documentElement.querySelector(
      '.v-dialog.v-overlay--active > .dialog',
    )
    if (el === null) {
      return
    }
    // @ts-expect-error
    el.style.right = val ? `${width * 0.5}px` : null
  })

  return { dialog, width }
}
