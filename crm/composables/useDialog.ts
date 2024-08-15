type DialogWidth = 'small' | 'default' | 'medium' | 'large' | 'x-large'

const dialogWidths: { [key in DialogWidth]: number } = {
  'small': 406,
  'default': 500,
  'medium': 800,
  'large': 1000,
  'x-large': 1300,
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
      // x-large не отодвигаем, не хватает места
      if (el.clientWidth === dialogWidths['x-large']) {
        return
      }
      // @ts-expect-error
      el.style.right = val ? `${width * 0.5}px` : null
    }
  })

  return { dialog, width, transition }
}
