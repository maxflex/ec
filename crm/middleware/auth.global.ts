export default defineNuxtRouteMiddleware(({ name }) => {
  const { $store } = useNuxtApp()
  console.log("user", $store.user)
  if ($store.user === null && name !== "login") {
    return navigateTo({ name: "login" })
  }
})
