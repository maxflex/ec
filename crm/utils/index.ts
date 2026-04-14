import type { FetchError } from 'ofetch'
import {
  differenceInDays,
  differenceInMinutes,
  differenceInMonths,
  differenceInWeeks,
  differenceInYears,
  format,
  getDay,
  getMonth,
  getYear,
} from 'date-fns'

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

export function getEntityStringFromToken(): EntityString | null {
  const token = useCookie('preview-token').value || useCookie('token').value
  if (token) {
    return token.split('|')[0] as EntityString
  }
  return null
}

/**
 * День недели по дате (пн, вт, ср..)
 */
export function formatWeekday(date: string): string {
  const day = (getDay(date) + 6) % 7

  return WeekdayLabel[day as Weekday]
}

export function getEntityString(entityType: EntityType): EntityString {
  return entityType.split('\\').slice(-1)[0].toLowerCase() as EntityString
}

let _newId = 0

export function newId(): number {
  _newId--
  return _newId
}

export function asInt(n: unknown) {
  return Number.parseInt(n as string) || 0
}

// export function selectItems2<T extends string | number>(labelObj: Record<T, string>, allowed?: Array<T>) {
//   const keys = allowed || Object.keys(labelObj).map(key => (Number.isNaN(key) ? key : Number(key)))

//   return keys.map(value => ({
//     value,
//     title: labelObj[value as T],
//   }))
// }

export function selectItems<T extends Record<string, any>>(
  labelObj: T,
  available: Array<keyof T> | undefined = undefined,
): SelectItems {
  let items = Object.entries(labelObj)
    .map(([value, title]) => ({
      title,
      value,
    }))

  if (available !== undefined) {
    items = items.filter(e => available.includes(e.value))
  }

  // если ключ – это число (например, годы '2024'), то приводим к числу, чтоб чётенько было
  // и сортируем (сверху дополнительно)
  if (items.length && Number.isFinite(+items[0].value)) {
    // @ts-expect-error
    items = items
      .map(({ value, title }) => ({
        title,
        value: Number.parseInt(value as string),
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
 * Новый академический год начинается с 1 июля
 */
export function getAcademicYear(d: string): Year {
  const year = getYear(d)
  const month = getMonth(d) + 1
  const academicYear = month >= 7 ? year : year - 1
  return academicYear as Year
}

/**
 * Текущий академический год
 */
export function currentAcademicYear(): Year {
  return getAcademicYear(today())
}

/**
 * Текущий месяц
 */
export function currentMonth(): Month {
  return (getMonth(new Date()) + 1) as Month
}

export function responseError(error: FetchError<any>) {
  useGlobalMessage(error.data!.message, 'error')
}

export function highlight(
  selector: string,
  className: 'item-updated' | 'item-deleted',
  scroll: ScrollBehavior | boolean = 'smooth',
) {
  nextTick(() => {
    const el = document?.querySelector(selector)
    scroll && el?.scrollIntoView({ block: 'center', behavior: scroll as ScrollBehavior })
    el?.classList.remove(className)
    setTimeout(() => el?.classList.add(className), 0)
  })
}

export function itemUpdated(entity: string, id: number, scroll: ScrollBehavior | boolean = 'smooth') {
  highlight(`#${entity}-${id}`, 'item-updated', scroll)
}

// не используется
// export async function itemDeleted(entity: string, id: number, scroll: ScrollBehavior | boolean = 'smooth') {
//   highlight(`${entity}-${id}`, 'item-deleted', scroll)
//   await new Promise((resolve) => {
//     setTimeout(resolve, 2000)
//   })
// }

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
  return format(dateTime, 'dd.MM.yy')
}

export function formatDateMob(dateTime: string | null): string {
  if (!dateTime) {
    return ''
  }
  return format(dateTime, 'dd.MM.yy')
}

export function formatDateMonth(dateTime: string | null): string {
  if (!dateTime) {
    return ''
  }
  return format(dateTime, 'dd.MM')
}

export function formatTime(time: string): string {
  return time.substring(0, 5)
}

export function formatDateTime(dateTime: string | null): string {
  if (!dateTime) {
    return ''
  }
  return format(dateTime, 'dd.MM.yy в HH:mm')
}

export function formatTextDate(date: string, year: boolean = false) {
  const month = getMonth(date) + 1
  const monthLabel = MonthLabelShort[month as Month]
  return format(date, `d ${monthLabel}${year ? ' yyyy' : ''}`)
}

/**
 * Форматировать дату в строку относительно текущего времени
 */
export function formatDateAgo(dateStr: string, withSuffix = false): string {
  const now = new Date()
  // Парсим строку один раз, чтобы все расчеты опирались на одну и ту же дату.
  const date = new Date(dateStr)

  // На невалидной дате показываем безопасный нейтральный текст.
  if (Number.isNaN(date.getTime())) {
    return 'недавно'
  }

  // Для будущих дат не показываем отрицательные "часы/минуты".
  const diffInMinutes = Math.max(0, differenceInMinutes(now, date))
  // Часы считаем из минут, чтобы не попадать в "0 часов" на границе округления.
  const diffInHours = Math.floor(diffInMinutes / 60)
  const diffInDays = differenceInDays(now, date)
  const diffInWeeks = differenceInWeeks(now, date)
  const diffInMonths = differenceInMonths(now, date)
  const diffInYears = differenceInYears(now, date)

  let result = 'недавно'

  if (diffInMinutes < 5) {
    result = 'недавно'
  }
  else if (diffInMinutes < 60) {
    result = plural(diffInMinutes, ['минута', 'минуты', 'минут'])
  }
  else if (diffInHours < 24) {
    result = plural(diffInHours, ['час', 'часа', 'часов'])
  }
  else if (diffInDays < 14) {
    result = plural(diffInDays, ['день', 'дня', 'дней'])
  }
  else if (diffInMonths < 1) {
    result = `${diffInWeeks} недели` // 2-4 недели
  }
  else if (diffInMonths < 12) {
    result = plural(diffInMonths, ['месяц', 'месяца', 'месяцев'])
  }
  else {
    result = plural(diffInYears, ['год', 'года', 'лет'])
  }

  return withSuffix && result !== 'недавно' ? `${result} назад` : result
}

export function formatPhone(number: unknown): string {
  const raw = typeof number === 'string' ? number.trim() : String(number ?? '')
  if (!raw) {
    return ''
  }

  const digits = raw.replace(/\D+/g, '')
  // Форматируем только российские номера; остальные (например, +44) оставляем как есть.
  if (digits.length !== 11 || !digits.startsWith('7')) {
    return `+${raw}`
  }

  return [
    '+7 (',
    digits.slice(1, 4),
    ') ',
    digits.slice(4, 7),
    '-',
    digits.slice(7, 9),
    '-',
    digits.slice(9, 11),
  ].join('')
}

export function formatName(
  person: HasName,
  format: NameFormat = 'last-first',
): string {
  let name: any[]
  switch (format) {
    case 'full':
      name = [person.last_name, person.first_name, person.middle_name]
      break

    case 'first-middle':
      name = [person.first_name, person.middle_name]
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
  const formatted = Math.abs(price).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ')

  return price < 0 ? `-${formatted}` : formatted
}

export function formatFileSize(file: UploadedFile) {
  const { size } = file
  const i = size === 0 ? 0 : Math.floor(Math.log(size) / Math.log(1024))
  const humanFileSize = Number.parseInt((size / 1024 ** i).toFixed(2))
  return (
    `${humanFileSize
    } ${
      ['байт', 'Кб', 'Мб', 'Гб', 'Тб'][i]}`
  )
}

export function filterAge(date: string) {
  const currentYear = Number.parseInt(format(new Date(), 'yyyy'))
  const year = Number.parseInt(date.split('-')[0])
  return plural(currentYear - year, ['год', 'года', 'лет'])
}

export function filterTruncate(text: string, stop: number, clamp = '...') {
  return text.slice(0, stop) + (stop < text.length ? clamp : '')
}

export function isDefined<T>(value: T | undefined | null): value is T {
  return value !== undefined && value !== null
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
