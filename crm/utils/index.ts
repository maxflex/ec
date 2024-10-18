import {
  differenceInDays,
  differenceInHours,
  differenceInMinutes,
  differenceInMonths,
  differenceInWeeks,
  differenceInYears,
  format,
  getMonth,
  getYear,
} from 'date-fns'
import { curry } from 'rambda'
import dayjs from 'dayjs'

export function today(): string {
  return format(new Date(), 'yyyy-MM-dd')
}

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

export function asInt(n: unknown) {
  return Number.parseInt(n as string) || 0
}

export function selectItems(obj: object, skip: string[] = []): SelectItems {
  const items = Object.entries(obj)
    .map(([value, title]) => ({
      value,
      title,
    }))
    .filter(e => !skip.includes(e.value))

  // если ключ – это число (например, годы '2024'),
  // то приводим к числу, чтоб чётенько было
  // и сортируем (сверху дополнительно)
  if (Number.isFinite(+items[0].value)) {
    return items
      .map(({ value, title }) => ({
        title,
        value: Number.parseInt(value),
      }))
      .sort((a, b) => b.value - a.value)
  }
  return items
}

export function yesNo(
  yesLabel: string = 'да',
  noLabel: string = 'нет',
  reverse: boolean = false,
): SelectItems {
  const items: SelectItems = [
    { value: 1, title: yesLabel },
    { value: 0, title: noLabel },
  ]
  return reverse ? items.reverse() : items
}

export function smoothScroll(
  scroll: 'dialog' | 'main',
  direction: 'top' | 'bottom',
  behavior: ScrollBehavior = 'smooth',
) {
  const querySelector = scroll === 'dialog' ? ' .dialog-body' : '.v-main'
  nextTick(() =>
    document
      .querySelector(querySelector)
      ?.scrollTo({ top: direction === 'bottom' ? 99999 : 0, behavior }),
  )
}

/**
 * Получить академический год по дате
 * Новый академический год начинается с 1 сентября
 */
export function getAcademicYear(d: string): Year {
  const year = getYear(d)
  const month = getMonth(d) + 1
  const academicYear = month >= 9 ? year : year - 1
  return academicYear as Year
}

/**
 * Текущий академический год
 */
export function currentAcademicYear(): Year {
  return getAcademicYear(today())
}

function getFiltersKey(tabName: string | null = null) {
  const route = useRoute()
  return [
    'filters',
    getEntityString(),
   `${tabName || ''}${String(route.name)}`,
  ].join('-')
}

/**
 * Сохранение и загрузка фильтров работает только у админа
 */
export function saveFilters(filters: object, tabName: string | null = null): void {
  localStorage.setItem(getFiltersKey(tabName), JSON.stringify(filters))
}

/**
 * Сохранение и загрузка фильтров работает только у админа
 */
export function loadFilters<T>(defaultFilters: T, tabName: string | null = null): T {
  const filters = localStorage.getItem(getFiltersKey(tabName))
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

export const debounce = curry((delay: number, fn: Function) => {
  let timeout: ReturnType<typeof setTimeout> | null = null
  return (...args: any[]) => {
    if (timeout) {
      clearTimeout(timeout)
    }
    timeout = setTimeout(() => fn(...args), delay)
  }
})

export function plural(
  number: number,
  words: string[],
  showNumber = true,
): string {
  if (number === undefined) {
    return ''
  }
  return (
    (showNumber ? `${number} ` : '')
    + words[
      number % 100 > 4 && number % 100 < 20
        ? 2
        : [2, 0, 1, 1, 1, 2][number % 10 < 5 ? Math.abs(number) % 10 : 5]
    ]
  )
}

export function formatDate(dateTime: string | null): string {
  if (!dateTime) {
    return ''
  }
  return dayjs(dateTime).format('DD.MM.YYYY')
}

export function formatTime(time: string): string {
  return time.substring(0, 5)
}

export function formatDateTime(dateTime: string | null): string {
  return dayjs(dateTime).format('DD.MM.YYYY в HH:mm')
}

export function formatDateMode(date: string, mode: StatsMode) {
  const dateObj = dayjs(date)
  const month = MonthLabelShort[getMonth(date)]
  switch (mode) {
    case 'day': return dateObj.format(`D ${month} YYYY`)
    case 'month': return dateObj.format(`${month} YYYY`)
    case 'year': return dateObj.format('YYYY год')
  }
}

export function formatTextDate(date: string, year: boolean = false) {
  const month = getMonth(date)
  return format(date, `d ${MonthLabelShort[month]}${year ? ' yyyy' : ''}`)
}

/**
 * Форматировать дату в строку относительно текущего времени
 */
export function formatDateAgo(dateStr: string): string {
  const now = new Date()

  const diffInMinutes = differenceInMinutes(now, dateStr)
  const diffInHours = differenceInHours(now, dateStr)
  const diffInDays = differenceInDays(now, dateStr)
  const diffInWeeks = differenceInWeeks(now, dateStr)
  const diffInMonths = differenceInMonths(now, dateStr)
  const diffInYears = differenceInYears(now, dateStr)

  if (diffInMinutes < 5) {
    return 'недавно'
  }

  if (diffInMinutes < 60) {
    return plural(diffInMinutes, ['минута', 'минуты', 'минут'])
  }

  if (diffInHours < 24) {
    return plural(diffInHours, ['час', 'часа', 'часов'])
  }

  if (diffInDays < 14) {
    return plural(diffInDays, ['день', 'дня', 'дней'])
  }

  if (diffInMonths < 1) {
    return `${diffInWeeks} недели` // 2-4 недели
  }

  if (diffInMonths < 12) {
    return plural(diffInMonths, ['месяц', 'месяца', 'месяцев'])
  }

  return plural(diffInYears, ['год', 'года', 'лет'])
}

export function formatPhone(number: string): string {
  return [
    '+7 (',
    number.slice(1, 4),
    ') ',
    number.slice(4, 7),
    '-',
    number.slice(7, 9),
    '-',
    number.slice(9, 11),
  ].join('')
}

export function formatYear(year: Year): string {
  return `${year}-${year + 1} уч. г.`
}

export function formatName(
  person: PersonResource,
  format: NameFormat = 'last-first',
): string {
  let name: any[]
  switch (format) {
    case 'full':
      name = [person.last_name, person.first_name, person.middle_name]
      break

    case 'initials':
      name = [person.last_name]
      if (person.first_name) {
        name.push(`${person.first_name[0]}.`)
      }
      if (person.middle_name) {
        name.push(`${person.middle_name[0]}.`)
      }
      break

    default:
      name = [person.last_name, person.first_name]
  }

  return name.join(' ')
}

export function formatNameInitials(person: PersonResource) {
  return [person.last_name, `${person.first_name![0]}.`, `${person.middle_name![0]}.`].join(' ')
}

export function formatFullName(person: PersonResource) {
  return [person.last_name, person.first_name, person.middle_name].join(' ')
}

export function formatPrice(price: number, showZero: boolean = false) {
  if (price === 0) {
    return showZero ? 0 : ''
  }
  return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ')
}

export function filterAge(date: string) {
  const currentYear = Number.parseInt(dayjs().format('YYYY'))
  const year = Number.parseInt(date.split('-')[0])
  console.log({ date, currentYear, year })
  return plural(currentYear - year, ['год', 'года', 'лет'])
}

export function formatClientTestResults(clientTest: ClientTestResource) {
  const score = clientTest.questions
    ?.filter((e, i) => clientTest.answers && e.answer === clientTest.answers[i])
    .reduce((c, v) => c + (v.score as number), 0)

  const total = clientTest.questions?.reduce(
    (c, v) => c + (v.score as number),
    0,
  )

  return `${score} из ${total}`
}

export function filterTruncate(text: string, stop: number, clamp = '...') {
  return text.slice(0, stop) + (stop < text.length ? clamp : '')
}

export function isDefined<T>(value: T | undefined | null): value is T {
  return value !== undefined && value !== null
}

export async function print(p: PrintOption, params: object = {}) {
  setTimeout(async () => {
    const { data } = await useHttp<{ text: string }>(`print/${p.id}`, { params })

    if (data.value) {
      // Create an invisible iframe
      const iframe = document.createElement('iframe')
      iframe.style.position = 'absolute'
      iframe.style.width = '0'
      iframe.style.height = '0'
      iframe.style.border = 'none'

      document.body.appendChild(iframe)

      // Get the iframe's document for injecting HTML content
      const iframeDocument = iframe.contentWindow?.document

      if (iframeDocument) {
        iframeDocument.open()
        iframeDocument.write(`
        <html lang="ru">
          <head>
            <title>ЕГЭ-Центр</title>
            <style>
              /* Add custom styles here if necessary */
              body { font-family: Arial, sans-serif; margin: 20px; }
              .print__contract h4 {text-align: center}
            </style>
          </head>
          <body>
            ${data.value.text}
          </body>
        </html>
      `)
        iframeDocument.close()

        // Wait for the content to load before printing
        iframe.onload = function () {
          iframe.contentWindow?.focus()
          iframe.contentWindow?.print()

          // Clean up by removing the iframe after printing
          document.body.removeChild(iframe)
        }
      }
      else {
        console.error('Failed to access iframe document.')
      }
    }
  }, 300)
}

/**
 * Превращает ключи с массивами в key[]
 * для правильной интерпретации массивов PHP-бэком
 */
export function transformArrayKeys(obj: Record<string, any>): Record<string, any> {
  const transformed: Record<string, any> = {}

  for (const key in obj) {
    const value = obj[key]

    if (Array.isArray(value)) {
      transformed[`${key}[]`] = value
    }
    else {
      transformed[key] = value
    }
  }

  return transformed
}
