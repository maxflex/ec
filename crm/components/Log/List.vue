<script setup lang="ts">
import type { UserDialog } from '#build/components'
import { mdiArrowTopRightThin, mdiCellphone, mdiLaptop, mdiSendCircle } from '@mdi/js'

const { items } = defineProps<{
  items: LogResource[]
}>()
const userDialog = ref<InstanceType<typeof UserDialog>>()
</script>

<template>
  <Table class="table--padding flex-start log-list">
    <TableRow v-for="log in items" :key="log.id">
      <TableCol :width="126" class="text-gray">
        {{ formatDateTime(log.created_at) }}
      </TableCol>
      <TableCol :width="20">
        <v-icon
          :icon="log.device === 'mobile' ? mdiCellphone : (log.device === 'telegram' ? mdiSendCircle : mdiLaptop)"
          :size="20"
          color="gray"
        />
      </TableCol>
      <TableCol :width="180">
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
          <div v-if="log.number" class="text-gray" style="font-size: 14px">
            {{ formatPhone(log.number) }}
          </div>
        </template>
        <template v-else>
          system
        </template>
      </TableCol>
      <TableCol :width="130">
        {{ LogTypeLabel[log.type] }}
        <v-tooltip v-if="log.emulation_user" location="bottom">
          <template #activator="{ props }">
            <v-icon icon="$preview" :size="20" color="gray" class="ml-1" v-bind="props" />
          </template>
          {{ formatName(log.emulation_user) }}
        </v-tooltip>
      </TableCol>
      <TableCol v-if="log.type === 'view'" class="text-truncate">
        <RouterLink :to="log.data.url!">
          {{ log.data.url! }}
        </RouterLink>
      </TableCol>
      <template v-else>
        <TableCol v-if="log.table" :width="150">
          {{ log.table }}
          <div v-if="log.row_id" style="font-size: 14px" class="text-gray">
            ID {{ log.row_id }}
          </div>
        </TableCol>
        <TableCol v-else :width="150">
          {{ log.ip }}
        </TableCol>
        <TableCol class="log-list__data">
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
        </TableCol>
      </template>
    </TableRow>
  </Table>
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
