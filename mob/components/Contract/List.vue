<script setup lang="ts">
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
  <div class="table table--padding table--hover" style="font-size: 14px">
    <div
      v-for="item in items"
      :id="`clients-${item.id}`"
      :key="item.id"
      @click="preview(item)"
    >
      <div style="width: 150px">
        {{ formatName(item.contract.client) }}
      </div>
      <div>
        <ContractDirections :item="item" />
      </div>
    </div>
  </div>
</template>
