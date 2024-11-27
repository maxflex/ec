import { useFetch } from '#app'

type useFetchType = typeof useFetch

export const useHttp: useFetchType = (path: string, options = {}) => {
  const { getCurrentToken, clearCurrentToken } = useAuthStore()
  const { showGlobalMessage } = useGlobalMessage()
  let baseURL = useRuntimeConfig().public.baseUrl
  const token = getCurrentToken().value

  if (token) {
    options.headers = { Authorization: `Bearer ${token}` }
    if (!path.startsWith('common/')) {
      baseURL += `${token.split('|')[0]}/`
    }
  }

  return useFetch(path, {
    ...options,
    baseURL,
    headers: token ? { Authorization: `Bearer ${token}` } : {},
    async onResponseError({ response: { status } }) {
      switch (status) {
        case 401:
          const route = useRoute()
          clearCurrentToken()
          if (route.name !== 'login') {
            sessionStorage.setItem('redirect', route.fullPath)
            window.location.href = '/login'
          }
          break

        case 404:
          showError({
            statusCode: status,
            statusMessage: 'Not found',
          })
          break

        case 500:
          showGlobalMessage('Ошибка сервера', 'error')
          break
      }
    },
  })
}
