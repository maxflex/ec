<script setup lang="ts">
const { items } = defineProps<{
  items: TelegramMessageResource[]
}>()
</script>

<template>
  <div class="table telegram-message-list">
    <div v-for="m in items" :key="m.id">
      <!-- <div style="width: 30px">
        {{ m.id }}
      </div> -->
      <div class="telegram-message-list__avatar-name">
        <UiAvatar :item="m.phone.entity" :size="40" />
        {{ formatName(m.phone.entity) }}
      </div>
      <div style="width: 170px">
        {{ formatPhone(m.phone.number) }}
      </div>
      <div style="flex: 1" class="text-truncate">
        {{ m.text }}
      </div>
      <div v-if="m.entry_id" style="width: 100px">
        <v-chip class="text-purple">
          группа {{ m.entry_id }}
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
