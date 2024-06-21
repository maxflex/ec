<script setup lang="ts">
import { mdiArrowTopRightThin } from '@mdi/js'
import type { UserDialog } from '#build/components'

const { items } = defineProps<{
  items: LogResource[]
}>()
const userDialog = ref<InstanceType<typeof UserDialog>>()

function getRouteName(log: LogResource) {
  if (log.entity_type === EntityType.client) {
    return 'clients-id'
  }
  return 'teachers-id'
}
</script>

<template>
  <div class="table log-list">
    <div v-for="log in items" :key="log.id">
      <div style="width: 170px" class="text-gray">
        {{ formatDateTime(log.created_at) }}
      </div>
      <div style="width: 180px">
        <a
          v-if="log.entity_type === EntityType.user"
          class="cursor-pointer"
          @click="userDialog?.edit(log.entity as UserResource)"
        >
          {{ formatName(log.entity) }}

        </a>
        <NuxtLink v-else :to="{ name: getRouteName(log), params: { id: log.entity.id } }">
          {{ formatName(log.entity) }}
        </NuxtLink>
        <div style="font-size: 14px" class="text-gray">
          {{ EntityTypeLabel[log.entity_type] }}
        </div>
      </div>
      <div style="width: 130px">
        {{ LogTypeLabel[log.type] }}
      </div>
      <div v-if="log.type === 'view'">
        <RouterLink :to="log.data.url!">
          {{ log.data.url! }}
        </RouterLink>
      </div>
      <template v-else>
        <div style="width: 150px">
          {{ log.table }}
          <div v-if="log.row_id" style="font-size: 14px" class="text-gray">
            ID {{ log.row_id }}
          </div>
        </div>
        <div class="log-list__data">
          <table v-if="log.type === 'update'">
            <tr v-for="(d, index) in log.data" :key="index">
              <td width="150">
                {{ d.field }}
              </td>
              <td>
                <span class="word-break text-gray">
                  {{ d.old }}
                </span>
                <v-icon :icon="mdiArrowTopRightThin" color="gray" />
                <span class="word-break"> {{ d.new }} </span>
              </td>
            </tr>
          </table>
          <table v-else>
            <tr v-for="(value, field, index) in log.data" :key="index">
              <td width="150" class="text-gray">
                {{ field }}
              </td>
              <td>
                {{ value }}
              </td>
            </tr>
          </table>
        </div>
      </template>
    </div>
  </div>
  <UserDialog ref="userDialog" />
</template>

<style lang="scss">
.log-list {
  .word-break {
    word-break: break-all;
  }
  &__data {
    table {
      & tr {
        & td {
          padding: 3px 0 !important;
          border-bottom-width: 0px !important;
          height: unset !important;
          font-size: 14px;
          &:first-child {
            padding-right: 30px !important;
          }
          .v-icon {
            font-size: 14px;
            transform: rotate(45deg);
            margin: 0 4px;
            top: -1px;
          }
        }
      }
    }
  }
}
</style>
