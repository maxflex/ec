<script setup lang="ts">
const { items } = defineProps<{
  items: ClientListResource[]
}>()

const { logIn } = useAuthStore()

async function preview(item: ClientListResource) {
  const { data } = await useHttp<TokenResponse>(
    `preview`,
    {
      method: 'post',
      body: {
        client_id: item.id,
      },
    },
  )
  const { token, user } = data.value!
  logIn(user, token, true)
}
</script>

<template>
  <div class="table table--padding">
    <div
      v-for="item in items"
      :id="`clients-${item.id}`"
      :key="item.id"
      @click="preview(item)"
    >
      <div style="width: 160px">
        {{ formatName(item) }}
      </div>
      <div>
        {{ item.directions.map(e => DirectionLabel[e]).join(', ') }}
      </div>
    </div>
  </div>
</template>
