export default defineNuxtRouteMiddleware((to) => {
  const { user } = useAuthStore()
  const isLoginPage = to.name === 'login'
  const isLoggedIn = user !== undefined

  if (!isLoggedIn && !isLoginPage) {
    return navigateTo({ name: 'login' })
  }
})
