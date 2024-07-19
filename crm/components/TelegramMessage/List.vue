<script setup lang="ts">
const { items } = defineProps<{
  items: TelegramMessageResource[]
}>()
</script>

<template>
  <div class="table">
    <div v-for="m in items" :key="m.id">
      <!-- <div style="width: 30px">
        {{ m.id }}
      </div> -->
      <div style="width: 40px">
        <UiAvatar :item="m.user" :size="40" />
      </div>
      <div style="width: 170px">
        {{ formatName(m.user) }}
      </div>
      <div style="flex: 1">
        {{ m.text }}
      </div>
      <div style="width: 100px">
        <v-chip v-if="m.entry_id" class="text-purple">
          групповое
        </v-chip>
      </div>
      <div style="flex: initial; width: 140px" class="text-gray">
        {{ formatDateTime(m.created_at) }}
      </div>
    </div>
  </div>
</template>
