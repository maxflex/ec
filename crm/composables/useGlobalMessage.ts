export const globalMessage = ref<{
  text: string
  color: GlobalMessageColor
  value: boolean
}>({
  text: '',
  color: undefined,
  value: false,
})

export default function (text: string, color: GlobalMessageColor = undefined) {
  globalMessage.value = {
    text,
    color,
    value: true,
  }
  setTimeout(() => (globalMessage.value.value = false), 5000)
}
