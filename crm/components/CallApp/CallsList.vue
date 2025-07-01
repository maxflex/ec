<script setup lang="ts">
import { mdiCallMade, mdiCallMissed, mdiCallReceived } from '@mdi/js'

const { items } = defineProps<{
  items: CallListResource[]
}>()
</script>

<template>
  <div class="calls-list">
    <div
      v-for="call in items"
      :key="call.id"
      class="calls-list__item"
    >
      <div>
        <div class="d-flex ga-2">
          <div class="calls-list__number">
            {{ formatPhone(call.number) }}
          </div>
          <div v-if="call.aon?.comment" class="calls-list__comment text-truncate">
            {{ call.aon.comment }}
          </div>
        </div>
        <div class="calls-list__date">
          {{ formatDateTime(call.created_at) }}
        </div>
      </div>
      <div>
        <CallAppPerson :item="call.aon" />
        <div v-if="call.answered_at">
          <CallAppDuration :item="call" />
        </div>
      </div>
      <div>
        <div
          v-if="call.is_missed"
          class="calls-list__user"
        >
          <v-icon
            :icon="mdiCallMissed"
            color="error"
          />
          Пропущенный
        </div>
        <div v-else-if="call.user" class="calls-list__user">
          <v-icon
            :icon="call.type === 'incoming' ? mdiCallReceived : mdiCallMade"
            :class="`calls-list--${call.type}`"
          />
          {{ formatName(call.user) }}
        </div>
        <div>
          <CallAppDownload v-if="call.has_recording" :item="call" />
        </div>
      </div>
    </div>
  </div>
</template>
