import { useFetch } from "#app"

type useFetchType = typeof useFetch

export const useHttp: useFetchType = (path, options = {}) => {
  const config = useRuntimeConfig()
  const token = useCookie("preview").value || useCookie("token").value
  const route = useRoute()
  const { logOut } = useAuthStore()

  return useFetch(path, {
    ...options,
    baseURL: config.public.baseUrl,
    headers: token ? { Authorization: `Bearer ${token}` } : {},
    async onResponseError({ response: { status } }) {
      if (status === 401) {
        const preview = useCookie("preview")
        if (preview.value) {
          preview.value = null
        } else {
          useCookie("token").value = null
        }
        logOut()
        if (route.name !== "login") {
          sessionStorage.setItem("redirect", route.fullPath)
          navigateTo({ name: "login" })
        }
      }
    },
  })
}
