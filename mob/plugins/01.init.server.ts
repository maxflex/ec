export default defineNuxtPlugin(async () => {
  const { getLoggedUser } = useAuthStore()
  await getLoggedUser()
})
