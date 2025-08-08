<script setup lang="ts">
import type { SwampListResource } from '.'
import { mdiArrowRightThin } from '@mdi/js'

const { items, group } = defineProps<{
  items: SwampListResource[]
  group?: GroupResource
}>()

const emit = defineEmits(['updated'])

const selectable: boolean = group !== undefined
const loading = ref(false)

async function select(item: SwampListResource) {
  if (!selectable || item.group !== null) {
    return
  }

  await useHttp(`client-groups`, {
    method: 'POST',
    body: {
      contract_version_program_id: item.id,
      group_id: group!.id,
    },
  })

  emit('updated')
}

async function removeFromGroup(item: SwampListResource) {
  if (!item.client_group_id) {
    return
  }

  loading.value = true
  await useHttp(`client-groups/${item.client_group_id}`, {
    method: 'delete',
  })

  emit('updated')
  loading.value = false
  useGlobalMessage(`Ученик удалён из ГР-${item.group!.id}`, 'success')
}
</script>

<template>
  <v-table class="swamp-list table-padding" :hover="selectable" :class="{ 'element-loading': loading }">
    <thead>
      <tr>
        <th width="100"></th>
        <th width="180"></th>
        <th width="150"></th>
        <th width="100"></th>
        <th width="60"></th>
        <th width="140"></th>
        <th width="100"></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="item in items" :key="item.id" :class="{ 'cursor-pointer': selectable }" @click="select(item)">
        <template v-if="item.group">
          <td width="100">
            <NuxtLink :to="{ name: 'groups-id', params: { id: item.group!.id } }">
              ГР-{{ item.group!.id }}
            </NuxtLink>
          </td>
          <td width="180">
            <GroupTeachers :item="item.group" />
          </td>
          <td>
            {{ ProgramShortLabel[item.program] }}
          </td>
          <td>
            <UiIfSet :value="item.group.lesson_counts.conducted || item.group.lesson_counts.planned">
              <template #empty>
                занятий нет
              </template>
              <GroupLessonCounts :item="item.group" />
            </UiIfSet>
          </td>
          <td>
            <UiIfSet :value="!!item.group.client_groups_count">
              <template #empty>
                0 уч.
              </template>
              {{ item.group.client_groups_count }} уч.
            </UiIfSet>
          </td>
          <td>
            <UiIfSet :value="Object.keys(item.group.teeth).length > 0">
              <template #empty>
                расписание отсутствует
              </template>
              <TeethAsText :items="item.group.teeth" />
            </UiIfSet>
          </td>
        </template>
        <template v-else>
          <td class="text-gray">
            Без группы
          </td>
          <td>
            <UiPerson v-if="selectable" :item="item.client" />
          </td>
          <td>
            {{ ProgramShortLabel[item.program] }}
          </td>
          <td colspan="3" />
        </template>
        <td colspan="2" style="position: relative;">
          <div class="pl-3">
            <div>
              {{ item.lessons_conducted }}
              <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
              {{ item.total_lessons }}
            </div>
            <div>
              {{ CvpStatusLabel[item.status] }}
            </div>
          </div>
          <div v-if="item.client_group_id" class="table-actionss">
            <v-btn
              color="error"
              icon="$minus"
              :size="48"
              @click="removeFromGroup(item)"
            />
          </div>
        </td>
      </tr>
    </tbody>
  </v-table>
</template>

<style lang="scss">
.swamp-list {
  thead {
    visibility: hidden;
    th {
      height: 0 !important;
      border: 0 !important;
    }
  }

  th,
  td {
    box-sizing: content-box;
  }

  .table-actionss {
    padding: 0 !important;
    display: flex;
    align-items: center;
    justify-content: flex-end;
  }
}
</style>
