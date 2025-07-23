<script setup lang="ts">
import type { SavedScheduleDraftResource } from '.'

const { items } = defineProps<{ items: SavedScheduleDraftResource[] }>()
const router = useRouter()
</script>

<template>
  <v-table hover>
    <tbody>
      <tr
        v-for="item in items" :key="item.id"
        class="cursor-pointer"
        @click="router.push({ name: 'schedule-drafts-id', params: { id: item.id } })"
      >
        <td width="30">
          {{ item.id }}
        </td>
        <td width="250">
          <template v-if="item.contract_id">
            Проект к договору №{{ item.contract_id }}
          </template>
          <template v-else>
            Новый договор
          </template>
        </td>
        <td width="200">
          <UiPerson :item="item.client" />
        </td>
        <td width="200">
          {{ plural(item.programs.length, ['программа', 'программы', 'программ']) }}
        </td>
        <td>
          {{ YearLabel[item.year] }}
        </td>
        <td width="300" class="text-gray">
          {{ formatName(item.user) }}
          {{ formatDateTime(item.created_at) }}
        </td>
      </tr>
    </tbody>
  </v-table>
</template>
