/**
 * Для кнопки "назад" в TG Mini-app
 */
export default defineNuxtRouteMiddleware((to) => {
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
