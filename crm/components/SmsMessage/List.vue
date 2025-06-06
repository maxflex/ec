<script setup lang="ts">
import type { SmsMessageListResource } from '.'

const { items } = defineProps<{
  items: SmsMessageListResource[]
}>()
</script>

<template>
  <div class="table">
    <div v-for="item in items" :key="item.id">
      <div style="width: 160px">
        {{ formatPhone(item.number) }}
      </div>
      <div class="text-truncate pr-4" style="flex: 1" :title="item.text">
        {{ item.text }}
      </div>
      <div :class="{ 'text-success': item.status === 1 }" class="text-lowercase" style="width: 200px">
        {{ item.status_name }}
      </div>
      <div class="text-gray" style="width: 130px; flex: initial">
        {{ formatDateTime(item.created_at) }}
      </div>
    </div>
  </div>
</template>
