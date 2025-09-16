<script setup lang="ts">
import type { SavedProjectResource } from '.'
import { mdiAlertBox } from '@mdi/js'

const { items } = defineProps<{ items: SavedProjectResource[] }>()
const emit = defineEmits<{
  delete: [e: SavedProjectResource]
}>()
const router = useRouter()
</script>

<template>
  <v-table class="table-padding saved-project-list" hover>
    <tbody>
      <tr
        v-for="item in items" :key="item.id"
        class="cursor-pointer"
        @click="router.push({ name: 'projects-editor', query: { id: item.id } })"
      >
        <td width="70">
          {{ item.id }}
        </td>

        <td width="200">
          <UiPerson v-if="item.client" :item="item.client" />
          <span v-else class="text-gray">без клиента</span>
        </td>

        <td width="150">
          {{ YearLabel[item.year] }}
        </td>

        <td width="200" :class="{ 'text-gray': item.is_archived }">
          <span v-if="item.contract_id">
            договор №{{ item.contract_id }}
          </span>
          <span v-else>
            новый договор
          </span>
          <v-badge
            v-if="item.changes > 0"
            color="orange-lighten-3"
            class="ml-1"
            inline
            :content="item.changes"
          ></v-badge>
          <v-icon
            v-if="item.has_problems_in_list"
            :icon="mdiAlertBox"
            color="error"
            class="ml-1"
          />
        </td>

        <td class="text-truncate">
          {{ item.comment }}
        </td>

        <td width="340" class="text-gray">
          {{ formatName(item.user) }}
          {{ formatDateTime(item.created_at) }}
          <div class="table-actionss" style="width: 200px" @click.stop="emit('delete', item)">
            <v-btn color="error" density="comfortable" style="top: 6px">
              удалить проект
            </v-btn>
          </div>
        </td>
      </tr>
    </tbody>
  </v-table>
</template>

<style lang="scss">
.saved-project-list {
  table {
    table-layout: fixed;
  }
}
</style>
