import dayjs from "dayjs"
import type { Phone, ClientTest } from "./models"

export function plural(
  number: number,
  words: string[],
  showNumber = true,
): string {
  if (number === undefined) {
    return ""
  }
  return (
    (showNumber ? number + " " : "") +
    words[
      number % 100 > 4 && number % 100 < 20
        ? 2
        : [2, 0, 1, 1, 1, 2][number % 10 < 5 ? Math.abs(number) % 10 : 5]
    ]
  )
}

export function formatDate(dateTime: NullableString): string {
  return dayjs(dateTime).format("DD.MM.YYYY")
}

export function formatDateTime(dateTime: NullableString): string {
  return dayjs(dateTime).format("DD.MM.YYYY в HH:mm")
}

export function formatPhone(number: string): string {
  return [
    "+7 (",
    number.slice(1, 4),
    ") ",
    number.slice(4, 7),
    "-",
    number.slice(7, 9),
    "-",
    number.slice(9, 11),
  ].join("")
}

export function humanFileSize(size: number) {
  var i = size == 0 ? 0 : Math.floor(Math.log(size) / Math.log(1024))
  return (
    (size / Math.pow(1024, i)).toFixed(2) * 1 +
    " " +
    ["байт", "Кб", "Мб", "Гб", "Тб"][i]
  )
}

export function formatYear(year: Year): string {
  return `${year}-${year + 1} уч. г.`
}

export function formatName(person: Person) {
  return [person.last_name, person.first_name].join(" ")
}

export function formatClientTestResults(clientTest: ClientTest) {
  const score = clientTest.questions
    ?.filter((e, i) => clientTest.answers && e.answer === clientTest.answers[i])
    .reduce((c, v) => c + (v.score as number), 0)

  const total = clientTest.questions?.reduce(
    (c, v) => c + (v.score as number),
    0,
  )

  return `${score} из ${total}`
}
