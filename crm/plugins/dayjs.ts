import dayjs from "dayjs"
import "dayjs/locale/ru"
// import utc from "dayjs/plugin/utc"
import timezone from "dayjs/plugin/timezone"
import duration from "dayjs/plugin/duration"

// dayjs.extend(utc)
dayjs.extend(timezone)
dayjs.extend(duration)

dayjs.locale("ru")
dayjs.tz.setDefault("Europe/Moscow")

export default defineNuxtPlugin(() => {
  return {
    provide: {
      dayjs,
      today: dayjs().format("YYYY-MM-DD"),
    },
  }
})
