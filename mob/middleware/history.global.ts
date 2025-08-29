/**
 * Для кнопки "назад" в TG Mini-app
 */
export default defineNuxtRouteMiddleware((to, from) => {
  console.log({ to, from })
  const { history, isGogingBack } = useHistory()

  if (isGogingBack.value) {
    isGogingBack.value = false
    return
  }

  switch (to.path) {
    case '/login':
      history.value = 0
      break

    case '/':
      break

    default:
      history.value++
  }
})
