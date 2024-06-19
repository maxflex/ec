import dayjs from 'dayjs'

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
  return dayjs(dateTime).format('DD.MM.YYYY')
}

export function formatTime(dateTime: string | null): string {
  return dayjs(dateTime).format('HH:mm')
}

export function formatDateTime(dateTime: string | null): string {
  return dayjs(dateTime).format('DD.MM.YYYY в HH:mm')
}

export function formatDateMode(date: string, mode: StatsMode) {
  const dateObj = dayjs(date)
  switch (mode) {
    case 'day': return dateObj.format('D MMM YYYY')
    case 'month': return dateObj.format('MMMM YYYY')
    case 'year': return dateObj.format('YYYY год')
  }
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

export function humanFileSize(size: number) {
  const i = size === 0 ? 0 : Math.floor(Math.log(size) / Math.log(1024))
  const humanFileSize = Number.parseInt((size / 1024 ** i).toFixed(2))
  return (
    `${humanFileSize
     } ${
     ['байт', 'Кб', 'Мб', 'Гб', 'Тб'][i]}`
  )
}

export function formatYear(year: Year): string {
  return `${year}-${year + 1} уч. г.`
}

export function formatName(person: PersonResource) {
  return [person.last_name, person.first_name].join(' ')
}

export function formatFullName(person: PersonResource) {
  return [person.last_name, person.first_name, person.middle_name].join(' ')
}

export function formatPrice(price: number) {
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

export function isDefined<T>(value: T | undefined | null): value is T {
  return value !== undefined && value !== null
}
