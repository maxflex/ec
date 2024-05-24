import { format } from 'date-fns'

export const today = () => format(new Date(), 'yyyy-MM-dd')

export function getCookie(key: string): object | null {
  const cookie = new Map<string, string>(
    // @ts-expect-error
    document.cookie
      .split('; ')
      .map(v => v.split(/=(.*)/s).map(decodeURIComponent)),
  )
  if (cookie.has(key)) {
    // @ts-expect-error
    return JSON.parse(cookie.get(key))
  }
  return null
}

export function getEntityString(): EntityString | null {
  const token = useCookie('preview-token').value || useCookie('token').value
  if (token) {
    return token.split('|')[0] as EntityString
  }
  return null
}

let _tmpId = 0

export function tmpId(): number {
  _tmpId--
  return _tmpId
}
