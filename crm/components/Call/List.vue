<script setup lang="ts">
import type { CallAppAonResource, CallListResource } from '~/components/CallApp'
import { mdiDotsHorizontal } from '@mdi/js'
import { CallerTypeLabel } from '~/components/CallApp'

const { items } = defineProps<{
  items: CallListResource[]
}>()

const router = useRouter()

const { user } = useAuthStore()

const showMoreBtn = [1, 5, 151].includes(user!.id)

function getPhoneItem(item: CallListResource): PhoneResource {
  if (item.aon) {
    return item.aon
  }

  // Для неизвестного номера создаем минимальный PhoneResource,
  // чтобы меню работало (звонок/копирование/история) без открытия старого диалога.
  const fallback: CallAppAonResource = {
    id: 0,
    number: item.number,
    comment: null,
    telegram_id: null,
    entity_type: EntityTypeValue.request,
    entity_id: 0,
    is_telegram_disabled: false,
  }

  return fallback
}
</script>

<template>
  <Table v-bind="$attrs" class="call-list">
    <TableRow v-for="item in items" :key="item.id">
      <TableCol :width="160">
        <PhoneList :items="[getPhoneItem(item)]" no-colors />
      </TableCol>
      <TableCol :width="150">
        <UiIfSet :value="item.caller_type">
          {{ CallerTypeLabel[item.caller_type!] }}
        </UiIfSet>
        <!-- <div v-if="item.aon?.comment" class="text-gray text-truncate">
          {{ item.aon.comment }}
        </div> -->
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
</template>
