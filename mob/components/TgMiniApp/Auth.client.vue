<script setup lang="ts">
import { useBackButton, useMiniApp } from 'vue-tg'

const { logInAndRemember } = useAuthStore()

const loading = ref(false)
const isError = ref(false)

const { user } = useMiniApp().initDataUnsafe

async function auth() {
  loading.value = true
  const { data, error } = await useHttp<TokenResponse>(
    `pub/auth/via-telegram`,
    {
      method: 'post',
      body: {
        initData: useMiniApp().initData,
      },
    },
  )

  if (error.value) {
    isError.value = true
    loading.value = false
    return
  }

  logInAndRemember(data.value!)
}

useBackButton().hide!()
</script>

<template>
  <v-window>
    <v-window-item>
      <v-card v-if="user" variant="tonal">
        <template #title>
          {{ user.first_name }}
          {{ user.last_name }}
        </template>
        <template #subtitle>
          @{{ user.username }}
        </template>
        <template #prepend>
          <UiAvatar :item="user" :size="63" class="mr-3" />
        </template>
      </v-card>
      <div v-if="isError" class="text-error text-center mt-3">
        ошибка доступа
      </div>
      <v-btn
        color="primary"
        :loading="loading"
        block
        size="x-large"
        @click="auth()"
      >
        Продолжить
      </v-btn>
    </v-window-item>
  </v-window>
</template>
