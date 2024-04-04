import { format } from "date-fns"

export const today = () => format(new Date(), "yyyy-MM-dd")

export function getCookie(key: string): object | null {
  const cookie = new Map<string, string>(
    // @ts-ignore
    document.cookie
      .split("; ")
      .map((v) => v.split(/=(.*)/s).map(decodeURIComponent)),
  )
  if (cookie.has(key)) {
    // @ts-ignore
    return JSON.parse(cookie.get(key))
  }
  return null
}

/**
 *
 * App\Models\User => admin
 * App\Models\Client = client
 * App\Models\Teacher = teacher
 */
export function getEntityString(user: User): EntityString {
  const entity = user.entity_type.split("\\")[2].toLowerCase()
  return entity === "user" ? "admin" : (entity as EntityString)
}
