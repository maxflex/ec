<script setup lang="ts">
import type { PassDialog } from '#components'
import type { PassResource } from '.'

const { items } = defineProps<{
  items: PassResource[]
}>()
const route = useRoute()
const showRequest = route.name === 'passes'
const passDialog = ref<InstanceType<typeof PassDialog>>()

function onPassUpdated(pass: PassResource) {
  const index = items.findIndex(p => p.id === pass.id)
  if (index === -1) {
    return
  }
  // eslint-disable-next-line
  items.splice(index, 1, pass)
  itemUpdated('pass', pass.id)
}

function onPassDeleted(pass: PassResource) {
  const index = items.findIndex(p => p.id === pass.id)
  if (index === -1) {
    return
  }
  // eslint-disable-next-line
  items.splice(index, 1)
}
</script>

<template>
  <v-table class="pass-list">
    <tbody>
      <tr v-for="item in items" :id="`pass-${item.id}`" :key="item.id">
        <td width="350">
          {{ item.comment }}
        </td>
        <td width="160">
          {{ formatDate(item.date) }}
        </td>
        <td v-if="showRequest" width="150">
          <RouterLink v-if="item.request" :to="{ name: 'requests-id', params: { id: item.request.id } }">
            заявка {{ item.request.id }}
          </RouterLink>
        </td>
        <td v-if="showRequest" width="160">
          <div v-if="item.request">
            {{ DirectionLabel[item.request.direction] }}
          </div>
        </td>
        <td class="flex-1">
          <span v-if="item.used_at">
            использован {{ formatDateTime(item.used_at) }}
          </span>
          <span v-else-if="item.is_expired" class="text-error">
            истёк
          </span>
          <span v-else class="text-gray">
            не использован
          </span>
        </td>
        <td class="text-gray" style="flex: initial; width: 160px">
          {{ formatDateTime(item.created_at!) }}
          <div class="table-actionss">
            <v-btn
              variant="plain"
              icon="$edit"
              :size="48"
              @click="passDialog?.edit(item)"
            />
          </div>
        </td>
      </tr>
      <slot />
    </tbody>
  </v-table>
  <PassDialog
    ref="passDialog"
    @updated="onPassUpdated"
    @deleted="onPassDeleted"
  />
</template>

<style lang="scss">
.pass-list {
  tr {
    position: relative;
  }
  .table-actionss {
    padding-top: 1px !important;
  }
}
</style>
