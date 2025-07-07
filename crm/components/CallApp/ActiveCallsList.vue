<script setup lang="ts">
import type { CallEvent } from '.'
import { mdiCallMade, mdiCallReceived } from '@mdi/js'

const { items } = defineProps<{
  items: CallEvent[]
}>()
const timerKey = computed(() => items.filter(e => e.state === 'Connected').length)
// const { user } = useAuthStore()
</script>

<template>
  <div class="active-calls">
    <div
      v-for="item in items" :key="item.number"
      class="calls-list__item"
    >
      <div>
        <div class="d-flex ga-2">
          <div class="calls-list__number">
            {{ formatPhone(item.number) }}
          </div>
          <div v-if="item.aon?.comment" class="calls-list__comment text-truncate">
            {{ item.aon.comment }}
          </div>
        </div>
      </div>
      <div>
        <CallAppPerson :item="item.aon" />
        <div>
          <CallAppCallTimer v-if="item.state === 'Connected'" :key="timerKey" :item="item" />
        </div>
      </div>
      <div>
        <transition name="call-title-transition">
          <div v-if="item.state === 'Connected'" :key="1">
            <v-icon
              :icon="item.type === 'incoming' ? mdiCallReceived : mdiCallMade"
              :class="`calls-list--${item.type}`"
            />
            {{ formatName(item.user!) }}
          </div>
          <div v-else :key="2">
            <v-icon :icon="mdiCallReceived" :class="`calls-list--${item.type}`" />
            Входящий
          </div>
        </transition>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.active-calls {
  .calls-list__item {
    background: rgba(var(--v-theme-primary), 0.2);
  }
}
</style>
