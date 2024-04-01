export default defineNuxtRouteMiddleware((to) => {
  const { $store } = useNuxtApp()
  const isLoginPage = to.name === "login"
  const isLoggedIn = $store.user !== null
  if (!isLoggedIn && !isLoginPage) {
    return navigateTo({ name: "login" })
  }
  if (isLoggedIn && isLoginPage) {
    return navigateTo({ name: "index" })
  }
  useHttp("logs", {
    method: "post",
    body: {
      url: to.path,
    },
  })
})
