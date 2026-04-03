<script setup lang="ts">
import type { CallAonResource, CallListResource } from '~/components/Call'
import { mdiDotsHorizontal, mdiTextBoxOutline, mdiWaveform } from '@mdi/js'
import { CallerTypeLabel } from '~/components/Call'

const { items } = defineProps<{
  items: CallListResource[]
}>()

const downloadingId = ref<number | null>(null)

const { user } = useAuthStore()

const showMoreBtn = [1, 5, 151].includes(user!.id)

function getPhoneItem(item: CallListResource): PhoneResource {
  if (item.aon) {
    return item.aon
  }

  // Для неизвестного номера создаем минимальный PhoneResource,
  // чтобы меню работало (звонок/копирование/история) без открытия старого диалога.
  const fallback: CallAonResource = {
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

async function downloadRecording(item: CallListResource) {
  if (!item.has_recording || downloadingId.value) {
    return
  }

  downloadingId.value = item.id
  try {
    const audio = await getAudio(item.id, 'download')
    const link = document.createElement('a')
    link.href = audio
    link.click()
  }
  catch (error: unknown) {
    const errorMessage = error instanceof Error && error.message
      ? error.message
      : 'Не удалось скачать аудиозапись'
    useGlobalMessage(errorMessage, 'error')
  }
  finally {
    setTimeout(() => {
      downloadingId.value = null
    }, 300)
  }
}

async function getAudio(callId: number, action: 'play' | 'download') {
  const { data, error } = await useHttp<string>(`calls/recording/${action}/${callId}`)

  if (error.value || !data.value) {
    throw new Error(error.value?.data?.message || 'Ошибка получения ссылки на запись')
  }

  return data.value
}
</script>

<template>
  <Table v-bind="$attrs" class="call-list">
    <TableRow v-for="item in items" :key="item.id">
      <TableCol :width="160">
        <PhoneList :items="[getPhoneItem(item)]" no-colors />
      </TableCol>
      <TableCol :width="160">
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
        <CallPerson :item="item.aon" class="text-truncate" />
      </TableCol>
      <TableCol :width="60">
        <div v-if="item.answered_at">
          <CallDuration :item="item" />
        </div>
      </TableCol>
      <TableCol :width="140">
        {{ formatDateTime(item.created_at) }}
      </TableCol>
      <TableCol :width="140" style="flex: initial !important">
        <div class="call-list__actions">
          <v-btn
            :size="42"
            :icon="mdiWaveform"
            variant="text"
            :disabled="!item.has_recording"
            :loading="downloadingId === item.id"
            @click="downloadRecording(item)"
          />
          <v-btn
            :size="42"
            class="no-pointer-events"
            :icon="mdiTextBoxOutline"
            variant="text"
            :disabled="!item.transcript"
          />
          <v-btn
            v-if="showMoreBtn"
            :size="42"
            :icon="mdiDotsHorizontal"
            color="black"
            variant="text"
            :to="{ name: 'calls-id', params: { id: item.id } }"
          />
        </div>
      </TableCol>
    </TableRow>
  </Table>
</template>

<style lang="scss">
.call-list {
  &__actions {
    button[disabled] {
      opacity: 0.1 !important;
      pointer-events: none;
    }
  }
}
</style>
