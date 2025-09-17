<script setup lang="ts">
import type { ProjectResource } from '.'
import { mdiAlertBox } from '@mdi/js'

const { items } = defineProps<{ items: ProjectResource[] }>()
const emit = defineEmits<{
  delete: [e: ProjectResource]
}>()
const router = useRouter()
</script>

<template>
  <v-table class="table-padding project-list" hover>
    <tbody>
      <tr
        v-for="item in items" :key="item.id"
        class="cursor-pointer"
        @click="router.push({ name: 'projects-editor', query: { id: item.id } })"
      >
        <td width="70">
          {{ item.id }}
        </td>

        <td width="220">
          <UiPerson v-if="item.client" :item="item.client" />
          <span v-else-if="item.name">
            {{ item.name }}
          </span>
          <span v-else class="text-gray">без клиента</span>
        </td>

        <td width="170">
          {{ YearLabel[item.year] }}
        </td>

        <td width="220" :class="{ 'text-gray': item.is_archived }">
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
            v-if="item.has_problems"
            :icon="mdiAlertBox"
            color="error"
            class="ml-1"
          />
        </td>

        <td>
          <div class="project-list__comments">
            <CommentBtn
              color="gray"
              :size="42"
              :class="{ 'no-items': item.comments_count === 0 }"
              :count="item.comments_count"
              :entity-id="item.id"
              :entity-type="EntityTypeValue.project"
            />
          </div>
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
.project-list {
  table {
    table-layout: fixed;
    overflow: hidden;
  }
  &__comments {
    position: absolute;
    top: 1px;
    left: 0;
    height: 100%;
    display: inline-flex;
    align-items: center;
  }
}
</style>
