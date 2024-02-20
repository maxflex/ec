export default function (w: number) {
  const dialog = ref(false)
  const width = w

  watch(dialog, (val) => {
    const el = document.documentElement.querySelector(
      ".v-dialog.v-overlay--active > .dialog",
    )
    if (el === null) {
      return
    }
    // @ts-expect-error
    el.style.right = val ? `${width * 0.5}px` : null
  })

  return { dialog, width }
}
