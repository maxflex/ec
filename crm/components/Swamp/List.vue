<script setup lang="ts">
import type { SwampListResource } from '.'

const { items } = defineProps<{
  items: SwampListResource[]
}>()

const loading = ref(false)
</script>

<template>
  <v-table class="swamp-list table-padding" :class="{ 'element-loading': loading }">
    <thead>
      <tr>
        <!-- ШИРИНЫ -->
        <th width="80"></th>
        <th width="170"></th>
        <th width="100"></th>
        <th width="80"></th>
        <th width="100"></th>
        <th width="60"></th>
        <th width="140"></th>
        <th width="150"></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="item in items" :key="item.id">
        <template v-if="item.group">
          <td>
            <NuxtLink :to="{ name: 'groups-id', params: { id: item.group!.id } }">
              ГР-{{ item.group!.id }}
            </NuxtLink>
          </td>
          <td>
            <GroupTeachers :item="item.group" />
          </td>
          <td>
            {{ ProgramShortLabel[item.program] }}
          </td>
          <td>
            <GroupFirstLessonDate :date="item.group.first_lesson_date" />
          </td>
          <td width="150">
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
          <td>
            <div v-for="c in item.group.cabinets" :key="c">
              <CabinetWithCapacity :item="c" />
            </div>
          </td>
        </template>
        <template v-else>
          <td class="text-gray">
            Без группы
          </td>
          <td>
          </td>
          <td>
            {{ ProgramShortLabel[item.program] }}
          </td>
          <td colspan="4" />
        </template>
        <td colspan="2">
          <ContractVersionProgramStatus :item="item" />
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
