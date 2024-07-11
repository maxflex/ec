<script setup lang="ts">
import { getDay } from 'date-fns'
import { uniq } from 'rambda'

const { id } = defineProps<{ id: number }>()
const items = ref<GroupVisitResource[]>([])
const dayLabels = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб']

const clients = computed(() => {
  const result: PersonResource[] = []
  for (const item of items.value) {
    for (const contractLesson of item.contractLessons) {
      result.push(contractLesson.client)
    }
  }
  return uniq(result)
})

const contractLessons = computed(() => {
  const result: {
    [key: number]: {
      [key: number]: {
        status: ContractLessonStatus
        is_remote: boolean
        minutes_late: number
      }
    }
  } = {}
  for (const item of items.value) {
    result[item.id] = {}
    for (const contractLesson of item.contractLessons) {
      result[item.id][contractLesson.client.id] = {
        status: contractLesson.status,
        is_remote: contractLesson.is_remote,
        minutes_late: contractLesson.minutes_late,
      }
    }
  }
  return result
})

async function loadData() {
  const { data } = await useHttp<GroupVisitResource[]>(`groups/visits/${id}`)
  if (data.value) {
    console.log('data', data.value)
    items.value = data.value
  }
}

nextTick(loadData)
</script>

<template>
  <v-table class="group-visits" hover>
    <tbody>
      <tr>
        <td />
        <td v-for="c in clients" :key="c.id">
          {{ c.last_name }} <br>
          <template v-if="c.first_name">
            {{ c.first_name[0] }}.
          </template>
          <template v-if="c.middle_name">
            {{ c.middle_name[0] }}.
          </template>
        </td>
      </tr>
      <tr v-for="l in items" :key="l.id">
        <td>
          {{ formatTextDate(l.dateTime) }}
          <span class="text-gray ml-1">
            {{ dayLabels[getDay(l.dateTime)] }}
          </span>
        </td>
        <td v-for="c in clients" :key="c.id" :class="{ 'is-remote': contractLessons[l.id][c.id] && contractLessons[l.id][c.id].is_remote }">
          <UiCircleStatus
            v-if="contractLessons[l.id][c.id]"
            :class="{
              'text-error': contractLessons[l.id][c.id].status === 'absent',
              'text-warning': contractLessons[l.id][c.id].status === 'late',
              'text-success': contractLessons[l.id][c.id].status === 'present',

            }"
          />
        </td>
      </tr>
    </tbody>
  </v-table>
</template>

<style lang="scss">
.group-visits {
  height: calc(100vh - 118px);
  table {
    table-layout: fixed;
    tr {
      td {
        width: 100px;
        border-bottom: 1px solid #e0e0e0 !important;
        &:first-child {
          width: 130px !important;
        }
      }
      &:first-child {
        position: sticky;
        top: 0;
        z-index: 2;
        td {
          background: white;
        }
      }
      &:not(:first-child) {
        td {
          &:first-child {
            position: sticky !important;
            z-index: 1;
            left: 0;
            background: white;
          }
        }
      }
    }
  }
  .is-remote {
    background: rgba(var(--v-theme-orange), 0.2);
  }
}
</style>
