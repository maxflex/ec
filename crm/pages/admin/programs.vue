<script setup lang="ts">
import { orderBy } from 'lodash-es'

interface ProgramTableResource {
  value: Program
  name: string
  human_name: string
  short_name: string
  direction_value: Direction
  direction_name: string
  direction_order: number
  exam_value: Exam | null
  exam_name: string | null
  duration: number
}

type ProgramTableField =
  | 'value'
  | 'name'
  | 'short_name'
  | 'human_name'
  | 'direction_name'
  | 'direction_value'
  | 'exam_name'
  | 'exam_value'
  | 'duration'

const { items, indexPageData } = useIndex<ProgramTableResource>(`programs`)

const tableFields: Array<{
  title: string
  field: ProgramTableField
}> = [
  { title: 'программа', field: 'value' },
  { title: 'название <br /> общее', field: 'name' },
  { title: 'название <br /> короткое', field: 'short_name' },
  { title: 'название <br /> человекочитаемое', field: 'human_name' },
  { title: 'направление', field: 'direction_name' },
  { title: '', field: 'direction_value' },
  { title: 'экзамен', field: 'exam_name' },
  { title: '', field: 'exam_value' },
  { title: 'длительность <br /> урока', field: 'duration' },
]

const sort = ref<{
  field: ProgramTableField
  direction: 'asc' | 'desc'
}>()

function toggleSort(field: ProgramTableField) {
  if (!sort.value || sort.value.field !== field) {
    sort.value = {
      field,
      direction: 'asc',
    }
  }
  else if (sort.value.direction === 'asc') {
    sort.value.direction = 'desc'
  }
  else {
    sort.value = undefined
  }
}

const sortedItems = computed(() => {
  if (!sort.value) {
    return items.value
  }

  const { field, direction } = sort.value

  return orderBy(items.value, x => x[field], direction)
})
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <v-table fixed-header height="100vh">
      <thead>
        <tr>
          <th
            v-for="h in tableFields"
            :key="h.field"
            class="sortable"
            :class="{
              'sortable--desc': sort?.direction === 'desc',
              'no-pointer-events': !h.title,
            }"
            @click="toggleSort(h.field)"
          >
            <span v-html="h.title" />
            <v-icon v-if="sort?.field === h.field" icon="$collapse" />
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in sortedItems" :key="item.value">
          <td>
            {{ item.value }}
          </td>
          <td>{{ item.name }}</td>
          <td>{{ item.short_name }}</td>
          <td>{{ item.human_name }}</td>
          <td>{{ item.direction_name }}</td>
          <td class="text-gray">
            {{ item.direction_value }}
          </td>
          <td>{{ item.exam_name || '' }}</td>
          <td class="text-gray">
            {{ item.exam_value || '' }}
          </td>
          <td>{{ item.duration }} мин</td>
        </tr>
      </tbody>
    </v-table>
  </UiIndexPage>
</template>

<style lang="scss">
.page-programs {
  // .v-table__wrapper {
  //   scrollbar-width: none;
  //   &::-webkit-scrollbar {
  //     width: 0;
  //   }
  // }
  table {
    table-layout: auto;
    tr {
      & > td {
        white-space: nowrap;
      }
      & > th {
        &:not(:last-child) {
          $width: 220px;
          width: $width;
          min-width: $width;
          max-width: $width;
        }
      }
      // & > td,
      // & > th {
      //   &:first-child {
      //     position: sticky;
      //     left: 0;
      //     background: white;
      //     border-right: 1px solid rgb(var(--v-theme-border));
      //   }
      // }
    }
  }
}
</style>
