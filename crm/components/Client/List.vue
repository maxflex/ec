<script setup lang="ts">
import type { ClientDialog } from '#build/components'
import type { ClientListResource } from '.'

const { items } = defineProps<{
  items: ClientListResource[]
}>()

const clientDialog = ref<InstanceType<typeof ClientDialog>>()
</script>

<template>
  <Table class="table--padding">
    <TableRow
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
      <TableCol :width="40">
        {{ item.id }}
      </TableCol>
      <TableCol :width="300">
        <NuxtLink :to="{ name: 'clients-id', params: { id: item.id } }">
          {{ formatName(item) }}
        </NuxtLink>
      </TableCol>
      <TableCol>
        <ClientDirections :items="item.directions" />
      </TableCol>
      <TableCol class="text-right text-gray">
        {{ formatDateTime(item.created_at) }}
      </TableCol>
    </TableRow>
  </Table>
  <ClientDialog ref="clientDialog" />
</template>
