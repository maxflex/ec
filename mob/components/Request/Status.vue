<script setup lang="ts">
import type { RequestListResource } from '.'

const { item } = defineProps<{ item: RequestListResource }>()
const status = ref<RequestStatus>(item.status)
const statuses = Object.keys(RequestStatusLabel) as RequestStatus[]

function toggle() {
  const nextIndex = statuses.findIndex(s => s === status.value) + 1
  status.value = statuses[nextIndex === statuses.length ? 0 : nextIndex]
  useHttp(`requests/${item.id}`, {
    method: 'put',
    body: {
      status: status.value,
    },
  })
}
</script>

<template>
  <div
    class="request-status"
    :class="`request-status--${status}`"
    @click="toggle()"
  />
</template>

<style lang="scss">
.request-status {
  --size: 10px;
  height: var(--size);
  width: var(--size);
  background: var(--background);
  border-radius: 50%;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: all ease-in-out 0.1s;
  &--new {
    --background: rgb(var(--v-theme-error));
  }
  &--awaiting {
    --background: rgb(var(--v-theme-orange));
  }
  &--finished {
    --background: rgba(var(--v-theme-success));
  }
  &--trash {
    --background: rgba(var(--v-theme-gray));
  }
  &:hover {
    transform: scale(1.2);
    &:before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(white, 0.2);
    }
  }
}
</style>
