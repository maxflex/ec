import type { UseFetchOptions } from '#app'
import { useFetch } from '#app'

export function useHttp<T = any>(
  path: string,
  options: UseFetchOptions<T> = {},
) {
  const { getCurrentToken, clearCurrentToken, getOriginalToken, isPreviewMode } = useAuthStore()
  const { public: { baseUrl, env } } = useRuntimeConfig()
  const token = getCurrentToken().value
  let url = baseUrl

  const headers: Record<string, string> = {
    Accept: 'application/json',
    ...(options.headers as Record<string, string> || {}),
  }

  if (token) {
    headers.Authorization = `Bearer ${token}`

    if (!path.startsWith('pub/')) {
      url += `${token.split('|')[0]}/`
    }
  }

  if (isPreviewMode) {
    const originalToken = getOriginalToken()

    if (originalToken) {
      headers.Preview = originalToken
    }
  }

  if (env === 'local') {
    options.credentials = 'include'
    // headers.Cookies = 'XDEBUG_SESSION=PHPSTORM'
    useCookie('XDEBUG_SESSION').value = 'PHPSTORM'
  }

  return useFetch(path, {
    ...options,
    baseURL: url,
    headers,
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

        case 403:
        case 404:
          showError({ statusCode: status })
          break

        case 500:
          useGlobalMessage('Ошибка сервера', 'error')
          break
      }
    },
  })
}
