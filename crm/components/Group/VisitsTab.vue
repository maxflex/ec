<script setup lang="ts">
import { getDay } from 'date-fns'
import { uniq } from 'rambda'

const { id } = defineProps<{ id: number }>()
const items = ref<GroupVisitResource[]>([])
const loading = ref(true)
const dayLabels = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб']

const clients = computed(() => {
  const result: PersonResource[] = []
  for (const item of items.value) {
    for (const clientLesson of item.clientLessons) {
      result.push(clientLesson.client)
    }
  }
  return uniq(result)
})

const clientLessons = computed(() => {
  const result: {
    [key: number]: {
      [key: number]: {
        status: ClientLessonStatus
        is_remote: boolean
        minutes_late: number
      }
    }
  } = {}
  for (const item of items.value) {
    result[item.id] = {}
    for (const clientLesson of item.clientLessons) {
      result[item.id][clientLesson.client.id] = {
        status: clientLesson.status,
        is_remote: clientLesson.is_remote,
        minutes_late: clientLesson.minutes_late,
      }
    }
  }
  return result
})

const teachers = computed(() => {
  const result: PersonResource[] = []
  for (const item of items.value) {
    result.push(item.teacher)
  }
  return uniq(result)
})

async function loadData() {
  loading.value = true
  const { data } = await useHttp<GroupVisitResource[]>(`groups/visits/${id}`)
  if (data.value) {
    items.value = data.value
  }
  loading.value = false
}

const noData = computed(() => !loading.value && items.value.length === 0)

nextTick(loadData)
</script>

<template>
  <UiIndexPage :data="{ loading, noData }">
    <v-table class="group-visits" hover>
      <colgroup>
        <col class="group-visits__col--date">
        <col v-for="t in teachers" :key="t.id" class="group-visits__col--teacher">
        <col v-for="c in clients" :key="c.id" class="group-visits__col--client">
      </colgroup>
      <tbody>
        <tr>
          <td />
          <td v-for="t in teachers" :key="t.id" class="group-visits__col--teacher">
            <div>
              {{ t.last_name }}
            </div>
            <div>
              {{ t.first_name![0] }}. {{ t.middle_name![0] }}.
            </div>
          </td>
          <td v-for="c in clients" :key="c.id">
            <div>
              {{ c.last_name }}
            </div>
            <div>
              {{ c.first_name }}
            </div>
          </td>
          <td />
        </tr>
        <tr v-for="l in items" :key="l.id">
          <td>
            {{ formatTextDate(l.dateTime) }}
            <span class="text-gray ml-1">
              {{ dayLabels[getDay(l.dateTime)] }}
            </span>
          </td>
          <td v-for="t in teachers" :key="t.id">
            <UiCircleStatus
              v-if="l.teacher.id === t.id"
              class="group-visits__teacher-status"
              :class="{
                'text-success': l.status === 'conducted',
                'text-error': l.status === 'cancelled',
                'text-gray': l.status === 'planned',
              }"
            />
          </td>
          <td v-for="c in clients" :key="c.id" :class="{ 'is-remote': clientLessons[l.id][c.id] && clientLessons[l.id][c.id].is_remote }">
            <UiCircleStatus
              v-if="clientLessons[l.id][c.id]"
              :class="{
                'text-error': clientLessons[l.id][c.id].status === 'absent',
                'text-warning': clientLessons[l.id][c.id].status === 'late',
                'text-success': clientLessons[l.id][c.id].status === 'present',
              }"
            />
          </td>
          <td />
        </tr>
      </tbody>
    </v-table>
  </UiIndexPage>
</template>

<style lang="scss">
.group-visits {
  height: calc(100vh - 142px);
  position: relative;
  table {
    table-layout: fixed;
    tr {
      td {
        width: 100px;
        border-bottom: 1px solid #e0e0e0 !important;
        border-right: 1px solid #e0e0e0;
        &:first-child {
          width: 130px !important;
          position: sticky !important;
          z-index: 1;
          left: 0;
          background: white;
        }
        &:last-child {
          width: auto !important;
        }
      }
      &:first-child {
        position: sticky;
        top: 0;
        z-index: 2;
        line-height: 22px;
        td {
          padding: 0 12px !important;
          background: white;
          & > div {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
          }
        }
      }
    }
  }
  .is-remote {
    background: rgba(var(--v-theme-orange), 0.2);
  }
  // &__teacher-status {
  //   .circle-status__circle {
  //     --size: 10px !important;
  //   }
  // }
  &__col {
    &--teacher {
      background: #f6f8fb !important;
    }
  }
}
</style>
