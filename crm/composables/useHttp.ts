import { useFetch } from "#app"

type useFetchType = typeof useFetch

export const useHttp: useFetchType = (path, options = {}) => {
  const { getCurrentToken, clearCurrentToken } = useAuthStore()
  const config = useRuntimeConfig()
  const token = getCurrentToken().value

  return useFetch(path, {
    ...options,
    baseURL: config.public.baseUrl,
    headers: token ? { Authorization: `Bearer ${token}` } : {},
    async onResponseError({ response: { status } }) {
      if (status === 401) {
        const route = useRoute()
        clearCurrentToken()
        if (route.name !== "login") {
          sessionStorage.setItem("redirect", route.fullPath)
          navigateTo({ name: "login" })
        }
      }
    },
  })
}
