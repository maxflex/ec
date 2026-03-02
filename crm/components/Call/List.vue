<script setup lang="ts">
import type { CallListResource } from '~/components/CallApp'
import { mdiCallMade, mdiCallMissed, mdiCallReceived, mdiFileDocument, mdiTextBoxSearchOutline } from '@mdi/js'

const { items } = defineProps<{
  items: CallListResource[]
}>()

const isDetailsDialogOpen = ref(false)
const selectedCall = ref<CallListResource | null>(null)

function openDetailsDialog(item: CallListResource): void {
  // Храним выбранный звонок, чтобы показать в диалоге именно его данные.
  selectedCall.value = item
  isDetailsDialogOpen.value = true
}
</script>

<template>
  <Table>
    <TableRow v-for="item in items" :key="item.id">
      <TableCol :width="150">
        {{ formatDateTime(item.created_at) }}
      </TableCol>
      <TableCol :width="170">
        {{ formatPhone(item.number) }}
      </TableCol>
      <TableCol :width="260">
        <div v-if="item.is_missed" class="calls-list__user">
          <template v-if="item.is_missed_callback">
            <v-icon
              :icon="mdiCallMissed"
              color="orange"
            />
            Перезвонили
          </template>
          <template v-else>
            <v-icon
              :icon="mdiCallMissed"
              color="error"
            />
            Пропущенный
          </template>
        </div>
        <div v-else-if="item.user" class="calls-list__user">
          <v-icon
            :icon="item.type === 'incoming' ? mdiCallReceived : mdiCallMade"
            :class="`calls-list--${item.type}`"
          />
          {{ formatName(item.user) }}
        </div>
      </TableCol>
      <TableCol>
        <CallAppPerson :item="item.aon" />
        <div v-if="item.aon?.comment" class="text-gray text-caption">
          {{ item.aon.comment }}
        </div>
      </TableCol>
      <TableCol :width="50">
        <div v-if="item.answered_at">
          <CallAppDuration :item="item" />
        </div>
      </TableCol>
      <TableCol class="call-list__actions">
        <div>
          <div>
            <v-btn
              v-if="item.transcription"
              :size="42"
              :icon="mdiFileDocument"
              @click="openDetailsDialog(item)"
            />
          </div>
          <div>
            <CallAppDownload v-if="item.has_recording" :item="item" />
          </div>
        </div>
      </TableCol>
    </TableRow>
  </Table>

  <v-dialog v-model="isDetailsDialogOpen" max-width="900">
    <div class="dialog-header">
      Просмотр разговора
      <v-btn icon="$close" :size="48" variant="text" @click="isDetailsDialogOpen = false" />
    </div>
    <div class="dialog-body">
      <h2>
        Краткое содержание
      </h2>
      <div class="text-pre-wrap">
        {{ selectedCall?.summary }}
      </div>
      <h2>
        Транскрибация
      </h2>
      <div class="text-pre-wrap">
        {{ selectedCall?.transcription }}
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.call-list {
  &__actions {
    flex: initial !important;
    width: 100px;

    & > div {
      display: flex;
      align-items: center;
      & > div {
        width: 42px;
      }
    }
  }
}
</style>
