<script setup lang="ts">
const { items } = defineProps<{ items: UserResource[] }>()
const emit = defineEmits<{
  edit: [userId: number]
}>()
</script>

<template>
  <div class="table table--actions-on-hover">
    <div
      v-for="item in items"
      :key="item.id"
    >
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          color="gray"
          @click="emit('edit', item.id)"
        />
      </div>
      <div style="width: 30px">
        {{ item.id }}
      </div>
      <div style="width: 300px">
        {{ formatName(item) }}
      </div>

      <div style="flex: 1">
        <PhoneList
          :items="item.phones"
          style="width: 250px"
        />
      </div>
      <div style="width: 300px">
        <span :class="{ 'text-gray': !item.is_active }">
          {{ UserStatusLabel[Number(item.is_active)] }}
        </span>
        <!-- <span v-if="item.is_active">
          действующий сотрудник
        </span>
        <span v-else class="text-gray">
          больше не работает
        </span> -->
      </div>
      <div
        class="text-gray"
        style="width: 150px; flex: initial"
      >
        {{ formatDateTime(item.created_at!) }}
      </div>
    </div>
  </div>
</template>
