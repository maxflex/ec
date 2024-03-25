import { useFetch } from "#app"

type useFetchType = typeof useFetch

export const useHttp: useFetchType = (path, options = {}) => {
  const config = useRuntimeConfig()
  const token = useCookie("preview").value || useCookie("token").value
  const { $store } = useNuxtApp()
  const route = useRoute()

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
        $store.user = null
        if (route.name !== "login") {
          sessionStorage.setItem("redirect", route.fullPath)
          navigateTo({ name: "login" })
        }
      }
    },
  })
}
