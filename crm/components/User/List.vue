<script setup lang="ts">
const { items } = defineProps<{ items: UserResource[] }>()
const emit = defineEmits<{ (e: 'edit', u: UserResource): void }>()
</script>

<template>
  <div class="table table--actions-on-hover">
    <div
      v-for="item in items"
      :key="item.id"
    >
      <div style="width: 30px">
        {{ item.id }}
      </div>
      <div style="width: 500px">
        {{ formatName(item) }}
      </div>
      <div
        class="text-gray"
        style="flex: 1"
      >
        {{ formatDateTime(item.created_at!) }}
      </div>
      <div
        style="width: 70px; flex: initial !important"
        class="text-right table-actions"
      >
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          color="gray"
          @click="emit('edit', item)"
        />
      </div>
    </div>
  </div>
</template>
