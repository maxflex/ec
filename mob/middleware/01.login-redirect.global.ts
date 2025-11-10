/**
 * Редирект с любой страницы на /login, если не авторизован
 */
export default defineNuxtRouteMiddleware((to) => {
  const { user, saveRedirectUrl } = useAuthStore()
  const isLoginPage = to.name === 'login'
  const isLoggedIn = user !== undefined

  if (!isLoggedIn && !isLoginPage) {
    /**
     * Чтобы после успешной авторизации попасить на страницу, куда изначально хотели зайти
     */
    saveRedirectUrl(to.fullPath)

    return navigateTo({ name: 'login' })
  }
})
