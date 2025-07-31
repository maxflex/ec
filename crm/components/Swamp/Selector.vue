<script setup lang="ts">
import type { ScheduleDraftStudent } from '../ScheduleDraft'
import type { SwampFilters } from './Filters.vue'
import { mdiArrowRightThin } from '@mdi/js'

const { group } = defineProps<{ group: GroupResource }>()

const emit = defineEmits(['back', 'updated'])

const filters = ref<SwampFilters>({
  year: group.year,
  program: [group.program!],
})

const loading = ref(false)

async function select(item: ScheduleDraftStudent) {
  if (item.group_id !== null) {
    return
  }

  await useHttp(
    `client-groups`,
    {
      method: 'POST',
      body: {
        contract_version_program_id: item.id,
        group_id: group!.id,
      },
    },
  )

  emit('updated')
}

const { items, indexPageData } = useIndex<ScheduleDraftStudent>(
  `swamps`,
  filters,
  {
    saveFilters: false,
    staticFilters: {
      group_id: group.id,
      students_tab: true,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <SwampFilters v-model="filters" disabled />
    </template>
    <template #buttons>
      <v-btn color="primary" @click="emit('back')">
        <template #prepend>
          <v-icon icon="$back" />
        </template>
        назад
      </v-btn>
    </template>
    <v-table
      class="swamp-students table-padding"
      hover
      :class="{ 'element-loading': loading }"
    >
      <tbody>
        <tr
          v-for="item in items"
          :key="item.id"
          :class="item.group_id === null ? 'cursor-pointer' : 'opacity-5 no-pointer-events'"
          @click="select(item)"
        >
          <td>
            <UiPerson :item="item.client" />
          </td>
          <td>
            <ScheduleDraftProblems :item="item" />
          </td>
          <td style="position: relative;">
            <div class="pl-3">
              <div>
                {{ item.swamp.lessons_conducted }}
                <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
                {{ item.swamp.total_lessons }}
              </div>
              <div>
                {{ SwampStatusLabel[item.swamp.status] }}
              </div>
            </div>
          </td>
        </tr>
      </tbody>
    </v-table>
  </UiIndexPage>
</template>
