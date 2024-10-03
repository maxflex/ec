<script setup lang="ts">
const { items } = defineProps<{
  items: TelegramMessageResource[]
}>()
</script>

<template>
  <div class="table telegram-message-list">
    <div v-for="m in items" :key="m.id">
      <div class="telegram-message-list__avatar-name">
        <UiPersonLink :item="m.phone.entity" :type="m.phone.entity_type" />
      </div>
      <div style="width: 170px">
        {{ formatPhone(m.phone.number) }}
      </div>
      <div style="flex: 1" class="text-truncate">
        {{ m.text }}
      </div>
      <div v-if="m.list_id" style="width: 100px">
        <v-chip class="text-purple">
          группа {{ m.list_id }}
        </v-chip>
      </div>
      <div v-if="m.template">
        <v-chip class="text-deepOrange">
          {{ TelegramTemplateLabel[m.template] }}
        </v-chip>
      </div>
      <div style="flex: initial; width: 140px" class="text-gray">
        {{ formatDateTime(m.created_at) }}
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.telegram-message-list {
  &__avatar-name {
    width: 200px;
    display: inline-flex;
    align-items: center;
    .v-avatar {
      margin-right: 10px;
    }
  }
}
</style>
