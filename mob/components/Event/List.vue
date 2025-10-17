<script setup lang="ts">
import type { EventListResource } from '.'
import { mdiCheckAll } from '@mdi/js'

const { items } = defineProps<{
  items: EventListResource[]
}>()

const router = useRouter()
</script>

<template>
  <div class="event-list">
    <EventItem
      v-for="item in items"
      :key="item.id"
      :item="item"
      @click="router.push({
        name: 'events-id',
        params: { id: item.id },
      })"
    />
  </div>
</template>

<style lang="scss">
.event-list {
  & > div {
    &:not(:last-child) {
      border-bottom: 1px solid rgb(var(--v-theme-border));
    }
  }
}
</style>
