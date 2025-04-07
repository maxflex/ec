<script setup lang="ts">
import type { UserDialog } from '#build/components'
import { mdiArrowTopRightThin, mdiCellphone } from '@mdi/js'

const { items } = defineProps<{
  items: LogResource[]
}>()
const userDialog = ref<InstanceType<typeof UserDialog>>()
</script>

<template>
  <div class="table log-list">
    <div v-for="log in items" :key="log.id">
      <div style="width: 126px" class="text-gray">
        {{ formatDateTime(log.created_at) }}
      </div>
      <div style="width: 20px">
        <v-icon v-if="log.is_mobile" :icon="mdiCellphone" color="gray" :size="20" />
      </div>
      <div style="width: 180px">
        <template v-if="log.entity">
          <a
            v-if="log.entity.entity_type === EntityTypeValue.user"
            class="cursor-pointer"
            @click="userDialog?.edit(log.entity.id)"
          >
            {{ formatName(log.entity) }}
          </a>
          <UiPerson v-else :item="log.entity" />
          <div style="font-size: 14px" class="text-gray">
            {{ EntityTypeLabel[log.entity.entity_type] }}
          </div>
        </template>
        <template v-else>
          system
        </template>
      </div>
      <div style="width: 130px">
        {{ LogTypeLabel[log.type] }}
        <v-tooltip v-if="log.emulation_user" location="bottom">
          <template #activator="{ props }">
            <v-icon icon="$preview" :size="20" color="gray" class="ml-1" v-bind="props" />
          </template>
          {{ formatName(log.emulation_user) }}
        </v-tooltip>
      </div>
      <div v-if="log.type === 'view'">
        <RouterLink :to="log.data.url!">
          {{ log.data.url! }}
        </RouterLink>
      </div>
      <template v-else>
        <div v-if="log.table" style="width: 150px">
          {{ log.table }}
          <div v-if="log.row_id" style="font-size: 14px" class="text-gray">
            ID {{ log.row_id }}
          </div>
        </div>
        <div v-else style="width: 150px">
          {{ log.ip }}
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
            <tr v-for="value, field in log.data" :key="field">
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
