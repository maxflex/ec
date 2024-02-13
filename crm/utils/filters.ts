import dayjs from "dayjs"
import type { Phone } from "./models"

export function formatDate(dateTime: NullableString): string {
  return dayjs(dateTime).format("DD.MM.YYYY")
}

export function formatDateTime(dateTime: NullableString): string {
  return dayjs(dateTime).format("DD.MM.YYYY в HH:mm")
}

export function formatPhone({ number }: Phone): string {
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

export function formatName(person: Person) {
  return [person.last_name, person.first_name].join(" ")
}
