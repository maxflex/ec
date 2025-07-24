<script setup lang="ts">
import type { SavedScheduleDraftResource } from '.'

const { items } = defineProps<{ items: SavedScheduleDraftResource[] }>()
const emit = defineEmits<{
  delete: [e: SavedScheduleDraftResource]
}>()
const router = useRouter()
</script>

<template>
  <v-table hover>
    <tbody>
      <tr
        v-for="item in items" :key="item.id"
        class="cursor-pointer"
        @click="router.push({ name: 'schedule-drafts-editor', query: { id: item.id } })"
      >
        <td width="200">
          Проект №{{ item.id }}
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
          <div class="table-actionss" style="width: 200px" @click.stop="emit('delete', item)">
            <v-btn color="error" density="comfortable">
              удалить проект
            </v-btn>
          </div>
        </td>
      </tr>
    </tbody>
  </v-table>
</template>
