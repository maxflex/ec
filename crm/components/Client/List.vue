<script setup lang="ts">
import type { ClientDialog } from '#build/components'
import type { ClientListResource } from '.'

const { items } = defineProps<{
  items: ClientListResource[]
}>()
const clientDialog = ref<InstanceType<typeof ClientDialog>>()
</script>

<template>
  <div class="table table--padding">
    <div
      v-for="item in items"
      :id="`clients-${item.id}`"
      :key="item.id"
    >
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          @click="clientDialog?.edit(item.id)"
        />
      </div>
      <div style="width: 40px">
        {{ item.id }}
      </div>
      <div style="width: 300px">
        <NuxtLink :to="{ name: 'clients-id', params: { id: item.id } }">
          {{ formatName(item) }}
        </NuxtLink>
      </div>
      <div>
        <ClientDirections :item="item.directions" />
      </div>
      <div class="text-right text-gray">
        {{ formatDateTime(item.created_at) }}
      </div>
    </div>
  </div>
  <ClientDialog ref="clientDialog" />
</template>
