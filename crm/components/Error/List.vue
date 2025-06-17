<script setup lang="ts">
import type { ErrorResource } from '.'

const { items } = defineProps<{
  items: ErrorResource[]
}>()
</script>

<template>
  <div class="table">
    <div v-for="item in items" :key="item.id">
      <div style="width: 300px;">
        <UiPerson :item="item.person" />
      </div>
      <div style="flex: 1">
        <template v-if="item.code === 1000">
          {{ item.number || 'телефон не указан' }}
        </template>
        <template v-else-if="item.code === 2000">
          договор №{{ item.entity_id }}
        </template>
      </div>
      <div style="width: 100px; flex: initial">
        <code>
          {{ item.code }}
        </code>
      </div>
    </div>
  </div>
</template>
