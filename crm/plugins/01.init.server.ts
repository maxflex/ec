export default defineNuxtPlugin(async function () {
  const { getLoggedUser } = useAuthStore()
  await getLoggedUser()
})
