<script setup lang="ts">
import type { TeacherPaymentResource } from '~/components/TeacherPayment'

const filters = useAvailableYearsFilter()

const { items, indexPageData, availableYears } = useIndex<TeacherPaymentResource>(
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
      <template #buttons>
        <UiCountDown :seconds="seconds" @timeout="checkVerification()">
          Просмотр разрешён ещё
        </UiCountDown>
      </template>
      <TeacherPaymentList :items="items" />
    </UiIndexPage>
  </template>
  <BalanceVerification v-else @verified="checkVerification()" />
</template>

<style lang="scss">
.page-payments.entity-teacher {
  .ui-countdown {
    color: rgb(var(--v-theme-gray));
    width: 280px;
  }
}
</style>
