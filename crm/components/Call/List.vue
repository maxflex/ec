<script setup lang="ts">
import type { CallListResource } from '~/components/CallApp'
import { mdiDotsHorizontal } from '@mdi/js'

const { items } = defineProps<{
  items: CallListResource[]
}>()

const router = useRouter()

const { user } = useAuthStore()

const showMoreBtn = [1, 5, 151].includes(user!.id)

function onRowClick(item: CallListResource) {
  if (!showMoreBtn) {
    return
  }
  router.push({
    name: 'calls-id',
    params: {
      id: item.id,
    },
  })
}
</script>

<template>
  <Table hoverable>
    <TableRow v-for="item in items" :key="item.id" class="cursor-pointer" @click="onRowClick(item)">
      <TableCol :width="160">
        {{ formatPhone(item.number) }}
      </TableCol>
      <TableCol :width="160">
        <div v-if="item.aon?.comment" class="text-gray text-truncate">
          {{ item.aon.comment }}
        </div>
      </TableCol>
      <TableCol :width="130">
        <div v-if="item.user">
          <span v-if="item.type === 'incoming'" class="text-success">
            Входящий
          </span>
          <template v-else>
            <span v-if="item.answered_at" class="text-success">
              Исходящий
            </span>
            <span v-else class="text-gray">
              Не дозвонились
            </span>
          </template>
        </div>
        <div v-else-if="item.is_missed">
          <span v-if="item.is_missed_callback" class="text-deepOrange">
            Перезвонили
          </span>
          <span v-else class="text-error">
            Пропущенный
          </span>
        </div>
      </TableCol>
      <TableCol :width="120">
        <template v-if="item.user">
          {{ formatName(item.user, 'initials') }}
        </template>
      </TableCol>
      <TableCol>
        <CallAppPerson :item="item.aon" class="text-truncate" />
      </TableCol>
      <TableCol :width="60">
        <div v-if="item.answered_at">
          <CallAppDuration :item="item" />
        </div>
      </TableCol>
      <TableCol :width="140" style="flex: initial !important">
        {{ formatDateTime(item.created_at) }}
      </TableCol>
    </TableRow>
  </Table>
</template>
