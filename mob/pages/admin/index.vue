<script setup lang="ts">
import type { ClientResource } from '~/components/Client'
import { mdiChevronRight } from '@mdi/js'
import { apiUrl } from '~/components/Client'

const { logIn } = useAuthStore()
const clientId = ref('')
const client = ref<ClientResource>()

async function loadClient() {
  if (!clientId.value) {
    return
  }
  const { data, error } = await useHttp<ClientResource>(`${apiUrl}/${clientId.value}`)
  if (error.value) {
    return
  }
  if (data.value) {
    client.value = data.value
  }
}

async function preview(phoneId: number) {
  const { data } = await useHttp<TokenResponse>(
    `preview-mode`,
    {
      method: 'post',
      body: {
        phone_id: phoneId,
      },
    },
  )
  const { token, user } = data.value!
  logIn(user, token, true)
}

watch(clientId, loadClient)
</script>

<template>
  <div class="index-page">
    <div class="index-page__content pa-4">
      <v-text-field v-model="clientId" label="Client ID" type="tel"></v-text-field>
    </div>
    <template v-if="client">
      <UiPageTitle>
        {{ formatName(client) }}
      </UiPageTitle>

      <div class="table table--padding table--hover">
        <div
          v-for="p in client.phones"
          :key="p.id"
          class="pr-2 cursor-pointer"
          @click="preview(p.id)"
        >
          <div style="width: 140px" :class="{ 'text-secondary': p.telegram_id }">
            {{ formatPhone(p.number) }}
          </div>
          <div style="flex: 1">
            {{ p.comment }}
          </div>
          <div style="width: 20px; flex: initial; align-self: center;" class="text-right">
            <v-icon :icon="mdiChevronRight" color="gray" />
          </div>
        </div>
      </div>
      <UiPageTitle class="mt-6">
        {{ formatName(client.representative) }}
      </UiPageTitle>
      <div class="table table--padding table--hover">
        <div
          v-for="p in client.representative.phones"
          :key="p.id"
          class="pr-2 cursor-pointer"
          @click="preview(p.id)"
        >
          <div style="width: 140px" :class="{ 'text-secondary': p.telegram_id }">
            {{ formatPhone(p.number) }}
          </div>
          <div style="flex: 1">
            {{ p.comment }}
          </div>
          <div style="width: 20px; flex: initial; align-self: center;" class="text-right">
            <v-icon :icon="mdiChevronRight" color="gray" />
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
