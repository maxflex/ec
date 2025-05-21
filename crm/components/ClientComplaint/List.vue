<script setup lang="ts">
import type { ClientComplaintListResource } from '.'

const { items, showClient } = defineProps<{
  items: ClientComplaintListResource[]
  showClient?: boolean
}>()

const emit = defineEmits<{
  edit: [id: number]
}>()
</script>

<template>
  <div class="table">
    <div v-for="item in items" :key="item.id">
      <div class="table-actionss">
        <v-btn
          variant="plain"
          icon="$edit"
          :size="48"
          @click="emit('edit', item.id)"
        />
      </div>
      <div v-if="showClient" style="width: 160px">
        <UiPerson :item="item.client" />
      </div>
      <div style="width: 180px">
        <UiPerson :item="item.teacher" />
      </div>
      <div style="width: 100px">
        {{ ProgramShortLabel[item.program] }}
      </div>
      <div class="text-truncate pr-2" style="flex: 1">
        {{ item.text }}
      </div>
      <div style="flex: initial; width: 130px" class="text-gray">
        {{ formatDateTime(item.created_at) }}
      </div>
    </div>
  </div>
</template>
