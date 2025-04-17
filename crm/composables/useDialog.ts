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
  // watch(dialog, (val) => {
  //   const lowerDialog = document.documentElement.querySelector<HTMLElement>(
  //     '.v-dialog.v-overlay--active > .dialog',
  //   )
  //   if (lowerDialog === null) {
  //     transition.value = 'dialog-fade-transition'
  //   }
  //   else {
  //     transition.value = 'dialog-second-transition'
  //     lowerDialog.style.right = val ? `${width * 0.5}px` : ''
  //   }
  // })

  return { dialog, width, transition }
}
