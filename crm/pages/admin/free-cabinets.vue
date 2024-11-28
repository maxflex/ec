<script setup lang="ts">
import { differenceInMinutes } from 'date-fns'

interface FreeCabinet {
  cabinet: Cabinet
  free_until: string | null
  is_busy: boolean
}

const { indexPageData, items } = useIndex<FreeCabinet>(`free-cabinets`)

function formatRemainingTime(item: FreeCabinet) {
  const now = new Date()
  const [hours, minutes, seconds] = item.free_until!.split(':').map(Number) // Extract time parts
  const until = new Date(
    now.getFullYear(),
    now.getMonth(),
    now.getDate(),
    hours,
    minutes,
    seconds,
  )

  const totalMinutes = differenceInMinutes(until, now)

  if (totalMinutes <= 0)
    return 'время истекло'

  const hoursLeft = Math.floor(totalMinutes / 60)
  const minutesLeft = totalMinutes % 60

  return [
    hoursLeft > 0 ? plural(hoursLeft, ['час', 'часа', 'часов']) : null,
    minutesLeft > 0 ? plural(minutesLeft, ['минута', 'минуты', 'минут']) : null,
  ]
    .filter(Boolean)
    .join(' ')
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <v-table>
      <tbody>
        <tr v-for="item in items" :key="item.cabinet">
          <td width="180">
            {{ CabinetLabel[item.cabinet] }}
          </td>
          <td width="200">
            <UiCircle v-if="item.is_busy" color="error">
              занят
            </UiCircle>
            <UiCircle v-else color="success">
              свободен
            </UiCircle>
          </td>
          <td width="200">
            <template v-if="!item.is_busy">
              <template v-if="item.free_until">
                до {{ formatTime(item.free_until) }}
              </template>
              <template v-else>
                до конца дня
              </template>
            </template>
          </td>
          <td>
            <template v-if="!item.is_busy && item.free_until">
              ещё {{ formatRemainingTime(item) }}
            </template>
          </td>
        </tr>
      </tbody>
    </v-table>
  </UiIndexPage>
</template>
