import { useFetch } from "#app"

type useFetchType = typeof useFetch

export const useHttp: useFetchType = (path, options = {}) => {
  const config = useRuntimeConfig()
  const token = useCookie("token").value

  return useFetch(path, {
    ...options,
    baseURL: config.public.baseUrl,
    headers: { Authorization: `Bearer ${token}` },
  })
}
