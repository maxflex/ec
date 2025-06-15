import type { UseFetchOptions } from '#app'
import { useFetch } from '#app'

export function useHttp<T = any>(
  path: string,
  options: UseFetchOptions<T> = {},
) {
  const { getCurrentToken, clearCurrentToken, getOriginalToken, isPreviewMode, clientParentId } = useAuthStore()
  let baseURL = useRuntimeConfig().public.baseUrl
  const token = getCurrentToken().value

  if (token && !path.startsWith('pub/')) {
    baseURL += `${token.split('|')[0]}/`
  }

  const headers: any = {
    Accept: 'application/json',
    ...(options.headers as any),
  }

  if (token) {
    headers.Authorization = `Bearer ${token}`
  }

  if (isPreviewMode) {
    headers.Preview = getOriginalToken()
  }

  if (clientParentId) {
    headers['Client-Parent-Id'] = clientParentId
  }

  return useFetch(path, {
    ...options,
    baseURL,
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
