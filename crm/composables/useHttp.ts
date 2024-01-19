import { useFetch } from "#app"

type useFetchType = typeof useFetch

export const useHttp: useFetchType = (path, options = {}) => {
  const {
    public: { baseUrl },
  } = useRuntimeConfig()
  // const token = useCookie("token").value
  console.log("so what", baseUrl, path, options)
  const response = useFetch(path, {
    ...options,
    baseURL: baseUrl,
    // headers: { Authorization: `Bearer ${token}` },
  })
  // fetch(`${baseUrl}/${path}`)
  console.log("response", response.data, process.client)
  return response
}
