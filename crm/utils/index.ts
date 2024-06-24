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

let _newId = 0

export function newId(): number {
  _newId--
  return _newId
}

export function selectItems(obj: object): SelectItems {
  const items = Object.entries(obj).map(([value, title]) => ({
    value,
    title,
  }))
  // если ключ – это число (например, годы '2024')
  // то приводим к числу, чтоб чётенько было
  // и сортирум (сверху дополнительно)
  if (Number.isFinite(+items[0].value)) {
    const sorted = items
      .map(({ value, title }) => ({
        title,
        value: Number.parseInt(value),
      }))
      .sort((a, b) => b.value - a.value)
    return sorted
  }
  return items
}

export function smoothScroll(
  scroll: 'dialog' | 'main',
  direction: 'top' | 'bottom',
) {
  const querySelector = scroll === 'dialog' ? ' .dialog-body' : '.v-main'
  nextTick(() =>
    document
      .querySelector(querySelector)
      ?.scrollTo({ top: direction === 'bottom' ? 9999 : 0, behavior: 'smooth' }),
  )
}

export function currentStudyYear(): Year {
  return 2024
}

function getFiltersKey() {
  const { name } = useRoute()
  return `filters-${String(name)}`
}

export function saveFilters<T>(filters: T): void {
  localStorage.setItem(getFiltersKey(), JSON.stringify(filters))
}

export function loadFilters<T>(defaultFilters: T): T {
  const filters = localStorage.getItem(getFiltersKey())
  return filters === null ? defaultFilters : JSON.parse(filters)
}

// status=math&status=eng ===> status[]=math&status[]=eng

export function filtersToQuery(filters: { [key: string]: any }) {
  const result: typeof filters = {}
  for (const key in filters) {
    const value = filters[key]
    const newKey = Array.isArray(value) ? `${key}[]` : key
    result[newKey] = value
  }
  return result
}

export function highlight(entity: string, id: number, className: 'item-updated' | 'item-deleted') {
  nextTick(() => {
    const el = document?.querySelector(`#${entity}-${id}`)
    el?.scrollIntoView({ block: 'center', behavior: 'smooth' })
    el?.classList.remove(className)
    setTimeout(() => el?.classList.add(className), 0)
  })
}

export function itemUpdated(entity: string, id: number) {
  highlight(entity, id, 'item-updated')
}

export async function itemDeleted(entity: string, id: number) {
  highlight(entity, id, 'item-deleted')
  await new Promise((resolve) => {
    setTimeout(resolve, 2000)
  })
}
