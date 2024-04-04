export default defineNuxtPlugin(async () => {
  const { data } = await useHttp<User>("auth/user")
  if (data.value) {
    const { logIn } = useAuthStore()
    logIn(data.value)
  }
})
