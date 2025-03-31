export default defineNuxtRouteMiddleware((to) => {
  const { user } = useAuthStore()
  const isLoginPage = to.name === 'login'
  const isLoggedIn = user !== undefined
  if (!isLoggedIn && !isLoginPage) {
    return navigateTo({ name: 'login' })
  }
  if (isLoggedIn) {
    if (isLoginPage) {
      return navigateTo({ name: 'index' })
    }
    // логирование просмотра URL
    useHttp(`logs`, {
      method: 'post',
      body: {
        url: to.path,
      },
    })
  }
})
