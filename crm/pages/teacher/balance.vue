<script setup lang="ts">
import { mdiLockOpenOutline } from '@mdi/js'

const { user } = useAuthStore()
const loading = ref(true)
const seconds = ref<number>(-1)

async function checkVerification() {
  loading.value = true
  const { data } = await useHttp<{ seconds: number }>(`balance-verification/check`)
  loading.value = false
  seconds.value = data.value!.seconds
}

nextTick(checkVerification)
</script>

<template>
  <UiLoader v-if="loading" />
  <template v-else-if="seconds > 0">
    <Balance :teacher-id="user?.id!">
      <UiCountDown :seconds="seconds" @timeout="checkVerification()">
        Просмотр разрешён ещё
      </UiCountDown>
    </Balance>
  </template>
  <BalanceVerification v-else @verified="checkVerification()" />
</template>

<style lang="scss">
.page-balance.entity-teacher {
  .ui-countdown {
    color: rgb(var(--v-theme-gray));
    width: 280px;
  }
}
</style>
