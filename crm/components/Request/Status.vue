<script setup lang="ts">
import type { RequestStatus } from '~/utils/models'

const { status } = defineProps<{ status: RequestStatus }>()
</script>

<template>
  <div
    class="request-status"
    :class="`request-status--${status}`"
  />
</template>

<style lang="sass">
.request-status
  --size: 10px
  height: var(--size)
  width: var(--size)
  background: var(--background)
  border-radius: 50%
  &--new
    --background: rgb(var(--v-theme-error))
  &--awaiting
    --background: rgb(var(--v-theme-orange))
  &--finished
    --background: rgba(var(--v-theme-success))
</style>
