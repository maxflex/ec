export default defineNuxtRouteMiddleware(({ name }) => {
  const { $store } = useNuxtApp()
  if ($store.user === null && name !== "login") {
    return navigateTo({ name: "login" })
  }
})
