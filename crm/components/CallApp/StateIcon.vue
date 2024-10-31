<script lang="ts" setup>
import { mdiPhone, mdiPhoneIncoming, mdiPhoneInTalk, mdiPhoneMissed } from '@mdi/js'
import { hasIncoming } from '.'

const { state } = defineProps<{
  state?: CallState
}>()
</script>

<template>
  <v-icon v-if="state === 'Connected'" :icon="mdiPhoneIncoming" />
  <v-icon v-else-if="state === 'Disconnected'" :icon="mdiPhoneMissed" />
  <v-icon v-else-if="hasIncoming" :icon="mdiPhoneInTalk" class="call-state-icon--has-incoming" />
  <v-icon v-else :icon="mdiPhone" />
</template>

<style lang="scss">
.call-state-icon {
  &--has-incoming {
    animation: ring 1s ease-in-out infinite;
  }
}
</style>
