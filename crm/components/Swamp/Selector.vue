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
    <v-table class="swamp-selector table-padding" :class="{ 'element-loading': loading }">
      <tbody>
        <tr
          v-for="item in items"
          :key="item.id"
          :class="{ 'swamp-selector--has-problems': item.has_problems }"
        >
          <td>
            <UiPerson :item="item.client" />
          </td>
          <td>
            договор №{{ item.contract_id }}
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

            <div v-if="!item.has_problems" class="table-actionss">
              <v-btn
                color="primary" density="comfortable" icon="$plus" :size="48"
                @click="select(item)"
              />
            </div>
          </td>
        </tr>
      </tbody>
    </v-table>
  </UiIndexPage>
</template>

<style lang="scss">
.swamp-selector {
  &--has-problems {
    td {
      opacity: 0.5;
    }
  }

  .table-actionss {
    width: 70px !important;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 0 !important;
  }
}
</style>
