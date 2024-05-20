import { useFetch } from '#app'

type useFetchType = typeof useFetch

export const useHttp: useFetchType = (path: string, options = {}) => {
  const { getCurrentToken, clearCurrentToken } = useAuthStore()
  let baseURL = useRuntimeConfig().public.baseUrl
  const token = getCurrentToken().value

  if (token) {
    options.headers = { Authorization: `Bearer ${token}` }
    if (!path.startsWith('common/')) {
      baseURL += token.split('|')[0] + '/'
    }
  }

  return useFetch(path, {
    ...options,
    baseURL,
    headers: token ? { Authorization: `Bearer ${token}` } : {},
    async onResponseError({ response: { status } }) {
      if (status === 401) {
        const route = useRoute()
        clearCurrentToken()
        if (route.name !== 'login') {
          sessionStorage.setItem('redirect', route.fullPath)
          navigateTo({ name: 'login' })
        }
      }
    },
  })
}
