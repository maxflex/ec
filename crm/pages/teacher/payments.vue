<script setup lang="ts">
import { mdiLockOpenOutline } from '@mdi/js'

const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}))
const { items, indexPageData, reloadData } = useIndex<TeacherPaymentResource, YearFilters>(
  `teacher-payments`,
  filters,
  {
    instantLoad: false,
  },
)

const loading = ref(true)
const seconds = ref<number>(-1)

async function checkVerification() {
  loading.value = true
  const { data } = await useHttp<{ seconds: number }>(`balance-verification/check`)
  loading.value = false
  seconds.value = data.value!.seconds
  if (seconds.value > 0) {
    reloadData()
  }
}

nextTick(checkVerification)
</script>

<template>
  <UiLoader v-if="loading" />
  <template v-else-if="seconds > 0">
    <UiIndexPage :data="indexPageData">
      <template #filters>
        <v-select
          v-model="filters.year"
          label="Учебный год"
          :items="selectItems(YearLabel)"
          density="comfortable"
        />
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
