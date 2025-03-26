<script setup lang="ts">
import { mdiLockOpenOutline } from '@mdi/js'

const filters = ref<AvailableYearsFilter>({
  year: undefined,
})

const { items, indexPageData, availableYears } = useIndex<TeacherPaymentResource, AvailableYearsFilter>(
  `teacher-payments`,
  filters,
  {
    loadAvailableYears: true,
  },
)

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
    <UiIndexPage :data="indexPageData">
      <template #filters>
        <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
      </template>
      <TeacherPaymentList :items="items" />
    </UiIndexPage>
    <UiCountDown :seconds="seconds" @timeout="checkVerification()">
      <v-icon :icon="mdiLockOpenOutline" color="gray" />
      Просмотр разрешён ещё
    </UiCountDown>
  </template>
  <BalanceVerification v-else @verified="checkVerification()" />
</template>

<style lang="scss">
.page-payments.entity-teacher {
  .ui-countdown {
    position: fixed;
    top: 30px;
    right: 0;
    z-index: 1;
    color: rgb(var(--v-theme-gray));
    width: 280px;
  }
}
</style>
