<script setup lang="ts">
import type { SwampFilters } from '~/components/Swamp/Filters.vue'
import { orderBy } from 'lodash-es'
import { Vue3SlideUpDown } from 'vue3-slide-up-down'

type Field = 'active_no_group' |
  'active_in_group' |
  'finished_no_group' |
  'finished_in_group' |
  'exceeded_no_group' |
  'exceeded_in_group' |
  'groups'

interface SwampByProgramResource {
  program: Program
  counts: Record<Field, number>
}

interface SwampClientResource {
  client: PersonResource
  groups: number[]
  counts: Record<Field, number>
}

const filters = ref<SwampFilters>(loadFilters({
  year: currentAcademicYear(),
  program: [],
}))

const expanded = ref<Partial<Record<Program, SwampClientResource[]>>>({})

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
  { field: 'groups', title: 'групп' },
]

const { items, indexPageData } = useIndex<SwampByProgramResource>(
  `swamps`,
  filters,
  {
    staticFilters: {
      by_program: 1,
    },
  },
)

function sum(field: Field) {
  return items.value.reduce((carry, x) => carry + x.counts[field], 0)
}

const programOrder = Object.keys(ProgramLabel) as Program[]

const sortedItems = computed(() => {
  if (!sort.value) {
    return [...items.value].sort(
      (a, b) => programOrder.indexOf(a.program) - programOrder.indexOf(b.program),
    )
  }

  const { field, direction } = sort.value

  return orderBy(items.value, x => x.counts[field], direction)
})

const isExpanded = (item: SwampByProgramResource): boolean => item.program in expanded.value

async function expand(item: SwampByProgramResource) {
  const { program } = item
  if (program in expanded.value) {
    delete expanded.value[program]
  }
  else {
    const params = { ...filters.value, program }
    const { data } = await useHttp<ApiResponse<SwampClientResource>>(
      `swamps`,
      {
        params,
      },
    )
    expanded.value[program] = data.value!.data
  }
}

watch(filters.value, () => (sort.value = undefined))
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <v-select
        v-model="filters.year"
        label="Учебный год"
        :items="selectItems(YearLabel)"
        density="comfortable"
      />
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
        <template v-for="item in sortedItems" :key="item.program">
          <tr :class="{ 'swamp-counts--expanded': isExpanded(item) }">
            <td>
              <!-- <RouterLink :to="{ name: 'swamps', query: { program: item.program } }"> -->
              <a
                class="swamp-counts__expand" @click="expand(item)"
              >
                {{ ProgramLabel[item.program] }}
                <v-icon icon="$collapse" />
              </a>
              <!-- </RouterLink> -->
            </td>
            <td v-for="{ field } in tableFields" :key="field" :class="`swamp-counts--${field}`" width="150">
              {{ formatPrice(item.counts[field]) }}
            </td>
          </tr>
          <template v-if="isExpanded(item)">
            <tr v-for="e in expanded[item.program]!" :key="e.client.id" class="swamp-counts__expansion">
              <td>
                <UiPerson :item="e.client" />
              </td>
              <td v-for="{ field } in tableFields" :key="field" :class="`swamp-counts--${field}`" width="150">
                <template v-if="field === 'groups'">
                  <div v-for="groupId in e.groups" :key="groupId">
                    <RouterLink :to="{ name: 'groups-id', params: { id: groupId } }">
                      ГР-{{ groupId }}
                    </RouterLink>
                  </div>
                </template>
                <template v-else>
                  {{ formatPrice(e.counts[field]) }}
                </template>
              </td>
            </tr>
          </template>
        </template>
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
    transition: none !important;
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

  &__expand {
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    user-select: none;

    .v-icon {
      font-size: 18px;
      transition: rotate ease-in-out 0.2s;
      rotate: 180deg;
    }
  }

  &--expanded,
  &__expansion {
    .v-icon {
      rotate: 0deg !important;
    }

    td {
      background: rgba(var(--v-theme-secondary), 0.1);
    }
  }
}
</style>
