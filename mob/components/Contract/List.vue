<script setup lang="ts">
import { mdiChevronRight } from '@mdi/js'

const { items } = defineProps<{
  items: ContractVersionListResource[]
}>()

const { logIn } = useAuthStore()

async function preview(item: ContractVersionListResource) {
  const { data } = await useHttp<TokenResponse>(
    `preview`,
    {
      method: 'post',
      body: {
        client_id: item.contract.client.id,
      },
    },
  )
  const { token, user } = data.value!
  logIn(user, token, true)
}
</script>

<template>
  <div class="table table--padding table--hover">
    <div
      v-for="item in items"
      :id="`clients-${item.id}`"
      :key="item.id"
      class="pr-2"
      @click="preview(item)"
    >
      <div style="width: 140px">
        {{ formatName(item.contract.client) }}
      </div>
      <div style="flex: 1">
        <div v-for="(value, d) in item.direction_counts" :key="d">
          {{ DirectionLabel[d] }} / {{ value }}
        </div>
      </div>
      <div style="width: 20px; flex: initial; align-self: center;" class="text-right">
        <v-icon :icon="mdiChevronRight" color="gray" />
      </div>
    </div>
  </div>
</template>
