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
import dayjs from 'dayjs'

export const menuCounts = ref<MenuCounts>({
  reports: 0,
})

export async function updateMenuCounts() {
  const { data } = await useHttp<MenuCounts>(`menu-counts`)
  menuCounts.value = data.value!
}

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

/**
 * Текущий месяц
 */
export function currentMonth(): Month {
  return (getMonth(new Date()) + 1) as Month
}

/**
 * @param tabName
 * @param routeName устанавливается только у админа в updateMenuCounts
 */
function getFiltersKey(
  tabName: string | null = null,
  routeName: string | null = null,
) {
  if (!routeName) {
    const route = useRoute()
    routeName = String(route.name)
  }
  return [
    'filters-2',
    getEntityStringFromToken(),
    `${tabName || ''}${routeName}`,
  ].join('-')
}

export function saveFilters(filters: object, tabName: string | null = null): void {
  localStorage.setItem(getFiltersKey(tabName), JSON.stringify(filters))
}

export function loadFilters<T>(defaultFilters: T, tabName: string | null = null): T {
  const filters = localStorage.getItem(getFiltersKey(tabName))
  return filters === null ? defaultFilters : JSON.parse(filters)
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

export function formatDateMonth(dateTime: string | null): string {
  if (!dateTime) {
    return ''
  }
  return dayjs(dateTime).format('DD.MM')
}

export function formatTime(time: string): string {
  return time.substring(0, 5)
}

export function formatDateTime(dateTime: string | null): string {
  return dayjs(dateTime).format('DD.MM.YY в HH:mm')
}

export function formatDateMode(date: string, mode: StatsMode) {
  const dateObj = dayjs(date)
  const month = getMonth(date) + 1
  const monthLabel = MonthLabelShort[month as Month]
  switch (mode) {
    case 'day': return dateObj.format(`D ${monthLabel} YYYY`)
    case 'week': return dateObj.format(`D ${monthLabel} YYYY`)
    case 'month': return dateObj.format(`${monthLabel} YYYY`)
    case 'year': return dateObj.format('YYYY год')
  }
}

export function formatTextDate(date: string, year: boolean = false) {
  const month = getMonth(date) + 1
  const monthLabel = MonthLabelShort[month as Month]
  return format(date, `d ${monthLabel}${year ? ' yyyy' : ''}`)
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
  if (price < 1) {
    return price
  }
  return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ')
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
