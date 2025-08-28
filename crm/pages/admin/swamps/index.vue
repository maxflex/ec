<script setup lang="ts">
import type { SwampFilters } from '~/components/Swamp/Filters.vue'
import { orderBy } from 'lodash-es'

type Field = 'active_no_group' |
  'active_in_group' |
  'finished_no_group' |
  'finished_in_group' |
  'exceeded_no_group' |
  'exceeded_in_group'

interface SwampCountsResource {
  client: PersonResource
  counts: Record<Field, number>
}

const route = useRoute()

const filters = ref<SwampFilters>(loadFilters({
  year: currentAcademicYear(),
  program: route.query.program ? [route.query.program as Program] : [],
}))

const groupsCount = ref<number>()

const sort = ref<{
  field: Field
  direction: 'asc' | 'desc'
}>()

function toggleSort(field: Field) {
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

const tableFields: Array<{
  title: string
  field: Field
}> = [
  { field: 'active_no_group', title: 'к исполнению' },
  { field: 'active_in_group', title: 'к исполнению <br/>в группе' },
  { field: 'finished_no_group', title: 'исполнено' },
  { field: 'finished_in_group', title: 'исполнено <br />в группе' },
  { field: 'exceeded_no_group', title: 'перевыполнено' },
  { field: 'exceeded_in_group', title: 'перевыполнено <br />в группе' },
]

const { items, indexPageData } = useIndex<SwampCountsResource>(
  `swamps`,
  filters,
  {
    saveFilters: false,
    staticFilters: {
      counts: 1,
    },
  },
)

function sum(field: Field) {
  return items.value.reduce((carry, x) => carry + x.counts[field], 0)
}

async function loadGroupsCount() {
  const { data } = await useHttp<number>(`groups`, {
    params: {
      ...filters.value,
      count: 1,
    },
  })
  groupsCount.value = Number.parseInt(data.value!)
}

const sortedItems = computed(() => {
  if (!sort.value) {
    return orderBy(items.value, x => x.client.last_name)
  }

  const { field, direction } = sort.value

  return orderBy(items.value, x => x.counts[field], direction)
})

watch(filters.value, () => (sort.value = undefined))
watch(filters.value, loadGroupsCount)

nextTick(loadGroupsCount)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <SwampFilters v-model="filters" />
      <div v-if="groupsCount !== undefined" class="text-gray">
        групп: {{ groupsCount }}
      </div>
    </template>

    <v-table height="calc(100vh - 81px)" class="swamp-counts">
      <thead>
        <tr>
          <th />
          <th
            v-for="h in tableFields"
            :key="h.field"
            class="sortable"
            :class="{
              'sortable--desc': sort?.direction === 'desc',
            }"
            @click="toggleSort(h.field)"
          >
            <span v-html="h.title" />
            <v-icon v-if="sort?.field === h.field" icon="$collapse" />
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in sortedItems" :key="item.client.id">
          <td>
            <UiPerson :item="item.client" />
          </td>
          <td v-for="{ field } in tableFields" :key="field" :class="`swamp-counts--${field}`" width="150">
            {{ formatPrice(item.counts[field]) }}
          </td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td />
          <td v-for="{ field } in tableFields" :key="field" :class="`swamp-counts--${field}`" width="150">
            {{ sum(field) || '' }}
          </td>
        </tr>
      </tfoot>
    </v-table>
  </UiIndexPage>
</template>

<style lang="scss">
.swamp-counts {
  td,
  th {
    border-right: 1px solid rgb(var(--v-theme-border));
  }

  thead {
    position: sticky;
    top: 0;
    z-index: 2;
    th {
      vertical-align: top !important;
      background: white;
    }
  }
  tfoot {
    td:first-child {
      background: white;
      // background: rgb(var(--v-theme-bg));
    }
    tr {
      position: sticky;
      bottom: 0;
    }
    td {
      background: white;
      font-weight: bold;
      border-top: 1px solid rgb(var(--v-theme-border)) !important;
    }
  }

  // active_no_group: 'к исполнению',
  // active_in_group: 'к исполнению <br/>в группе',
  // finished_no_group: 'исполнено',
  // finished_in_group: 'исполнено <br />в группе',
  // exceeded_no_group: 'перевыполнено',
  // exceeded_in_group: 'перевыполнено <br />в группе',
  &--active_no_group,
  &--active_in_group {
    color: rgb(var(--v-theme-deepOrange));
  }

  &--finished_no_group,
  &--finished_in_group {
    color: rgb(var(--v-theme-success));
  }

  &--exceeded_no_group,
  &--exceeded_in_group {
    color: rgb(var(--v-theme-error));
  }
}
</style>
