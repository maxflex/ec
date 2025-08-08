<!-- добавить ученика в текущую группу -->
<script setup lang="ts">
import type { AddToGroupItem } from '.'
import type { SwampFilters } from '../Filters.vue'
import { mdiArrowRightThin } from '@mdi/js'

const { group } = defineProps<{ group: GroupResource }>()

const emit = defineEmits(['back', 'updated'])

const filters = ref<SwampFilters>({
  year: group.year,
  program: [group.program!],
})

const loading = ref(false)
const saving = ref(false)

const selected = ref<Record<number, boolean>>({})
const selectedCount = computed<number>(() => Object.keys(selected.value).length)

function toggle(item: AddToGroupItem) {
  if (item.id in selected.value) {
    delete selected.value[item.id]
  }
  else {
    selected.value[item.id] = true
  }
}

async function apply() {
  if (selectedCount.value === 0) {
    return
  }

  saving.value = true

  await useHttp(
    `client-groups`,
    {
      method: 'POST',
      body: {
        ids: Object.keys(selected.value),
        group_id: group!.id,
      },
    },
  )

  emit('updated')

  saving.value = false
}

function isSelected(item: AddToGroupItem) {
  if (item.id in selected.value) {
    return true
  }
  if (item.group_id === group.id) {
    return true
  }
  return false
}

const { items, indexPageData } = useIndex<AddToGroupItem>(
  `swamps`,
  filters,
  {
    saveFilters: false,
    staticFilters: {
      group_id: group.id,
      add_to_group: true,
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
      <div class="d-flex ga-4">
        <v-btn variant="text" @click="emit('back')">
          отмена
        </v-btn>
        <v-btn color="primary" :loading="saving" :width="170" @click="apply()">
          применить
          <span v-if="selectedCount > 0" class="pl-2 opacity-5">
            {{ selectedCount }}
          </span>
        </v-btn>
      </div>
    </template>
    <v-table class="swamp-selector table-padding" :class="{ 'element-loading': loading }">
      <tbody>
        <tr
          v-for="item in items"
          :key="item.id"
          :class="{
            'swamp-selector--has-problems': item.has_problems,
            'changed': item.id in selected,
          }"
        >
          <td width="230">
            <UiPerson :item="item.client" />
          </td>
          <td width="480">
            <TeethBar :items="item.teeth" :current="group.teeth!" />
          </td>
          <td width="160">
            договор №{{ item.contract_id }}
          </td>
          <td>
            <ContractVersionProgramStatus :item="item" />
          </td>
          <td widht="200">
            <SwampAddToGroupProblems v-if="item.has_problems" :item="item" :group-id="group.id" />
          </td>
          <td>
            <v-switch
              :class="{ 'no-pointer-events': item.has_problems }"
              :model-value="isSelected(item)"
              :true-value="true"
              color="success"
              hide-details
              inset
              density="compact"
              @click="toggle(item)"
            ></v-switch>
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
}
</style>
