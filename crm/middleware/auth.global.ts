export default defineNuxtRouteMiddleware(({ name }) => {
  const { $store } = useNuxtApp()
  const isLoginPage = name === "login"
  const isLoggedIn = $store.user !== null
  if (!isLoggedIn && !isLoginPage) {
    return navigateTo({ name: "login" })
  }
  if (isLoggedIn && isLoginPage) {
    return navigateTo({ name: "index" })
  }
})
