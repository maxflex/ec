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
      <div style="width: 200px">
        <UiAvatar :item="m.phone.entity" :size="40" class="mr-2" />
        {{ formatName(m.phone.entity) }}
      </div>
      <div style="width: 170px">
        {{ formatPhone(m.phone.number) }}
      </div>
      <div style="flex: 1">
        {{ m.text }}
      </div>
      <div style="width: 100px">
        <v-chip v-if="m.entry_id" class="text-purple">
          группа {{ m.entry_id }}
        </v-chip>
      </div>
      <div v-if="m.user" style="width: 200px">
        <UiAvatar :item="m.user" :size="40" class="mr-2" />
        {{ formatName(m.user) }}
      </div>
      <div style="flex: initial; width: 140px" class="text-gray">
        {{ formatDateTime(m.created_at) }}
      </div>
    </div>
  </div>
</template>
