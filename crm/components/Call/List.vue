<script setup lang="ts">
import type { PhoneDialog } from '#build/components'
import type { CallListResource } from '~/components/CallApp'
import { mdiDotsHorizontal } from '@mdi/js'

const { items, clickable } = defineProps<{
  items: CallListResource[]
  clickable?: boolean
}>()

const router = useRouter()

const { user } = useAuthStore()
const phoneDialog = ref<InstanceType<typeof PhoneDialog>>()

const showMoreBtn = [1, 5, 151].includes(user!.id)
</script>

<template>
  <Table v-bind="$attrs">
    <TableRow v-for="item in items" :key="item.id">
      <TableCol :width="160">
        <a v-if="clickable" class="cursor-pointer" @click.stop="phoneDialog?.open(item.aon || item, item.aon?.entity)">
          {{ formatPhone(item.number) }}
        </a>
        <span v-else>
          {{ formatPhone(item.number) }}
        </span>
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
      <TableCol :width="140">
        {{ formatDateTime(item.created_at) }}
      </TableCol>
      <TableCol :width="50" style="flex: initial !important">
        <v-btn
          v-if="showMoreBtn"
          :size="42"
          :icon="mdiDotsHorizontal"
          @click="router.push({ name: 'calls-id', params: { id: item.id } })"
        />
      </TableCol>
    </TableRow>
  </Table>
  <PhoneDialog ref="phoneDialog" />
</template>
