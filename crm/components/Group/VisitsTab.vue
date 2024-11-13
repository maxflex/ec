<script setup lang="ts">
import { getDay } from 'date-fns'
import { uniq } from 'rambda'

const { id } = defineProps<{ id: number }>()
const items = ref<GroupVisitResource[]>([])
const loading = ref(true)

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
        minutes_late: number
      }
    }
  } = {}
  for (const item of items.value) {
    result[item.id] = {}
    for (const clientLesson of item.clientLessons) {
      result[item.id][clientLesson.client.id] = {
        status: clientLesson.status,
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

function isRemote(l: GroupVisitResource, c: PersonResource): boolean {
  if (!clientLessons.value || !clientLessons.value[l.id] || !clientLessons.value[l.id][c.id]) {
    return false
  }
  const { status } = clientLessons.value[l.id][c.id]
  return ['lateOnline', 'presentOnline'].includes(status)
}

function getClientLessonColor(l: GroupVisitResource, c: PersonResource) {
  if (!clientLessons.value || !clientLessons.value[l.id] || !clientLessons.value[l.id][c.id]) {
    return ''
  }
  const { status } = clientLessons.value[l.id][c.id]
  switch (status) {
    case 'absent':
      return 'error'

    case 'late':
    case 'lateOnline':
      return 'warning'

    default:
      return 'success'
  }
}

function getTeacherColor(l: GroupVisitResource) {
  switch (l.status) {
    case 'conducted':
      return 'success'
    case 'planned':
      return 'gray'
    case 'cancelled':
      return 'error'
  }
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
              <div>
                {{ t.last_name }}
              </div>
              <div>
                {{ t.first_name }} {{ t.middle_name![0] }}.
              </div>
            </div>
          </td>
          <td v-for="c in clients" :key="c.id">
            <div>
              <div>
                {{ c.last_name }}
              </div>
              <div>
                {{ c.first_name }}
              </div>
            </div>
          </td>
          <td />
        </tr>
        <tr v-for="l in items" :key="l.id">
          <td>
            {{ formatTextDate(l.dateTime) }}
            <span class="text-gray ml-1">
              {{ WeekdayLabel[getDay(l.dateTime) as Weekday] }}
            </span>
          </td>
          <td v-for="t in teachers" :key="t.id">
            <UiCircle
              v-if="l.teacher.id === t.id"
              :color="getTeacherColor(l)"
            />
          </td>
          <td v-for="c in clients" :key="c.id" :class="{ 'is-remote': isRemote(l, c) }">
            <UiCircle
              v-if="clientLessons[l.id][c.id]"
              :color="getClientLessonColor(l, c) "
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
  height: calc(100vh - 118px);
  position: relative;
  td .circle {
    justify-content: center;
  }
  table {
    table-layout: fixed;
    tr {
      td {
        width: 60px;
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
          padding: 8px 12px !important;
          background: white;
          vertical-align: bottom;
          font-size: 14px;
          & > div {
            display: flex;
            line-height: 16px;
            //letter-spacing: -2px;
            & > div {
              cursor: default;
              writing-mode: vertical-rl;
              transform: rotate(180deg);
              max-height: 100px;
              white-space: nowrap !important;
              overflow: hidden !important;
              text-overflow: ellipsis !important;
            }
          }
        }
      }
    }
  }
  .is-remote {
    &:before {
      position: absolute;
      content: '';
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(var(--v-theme-orange), 0.2);
    }
  }
  &__col {
    &--teacher {
      background: #f6f8fb !important;
    }
  }
  .circle-status {
    justify-content: center;
  }
}
</style>
