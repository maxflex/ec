<script setup lang="ts">
import type { SmsMessageListResource } from '~/components/SmsMessage'

const filters = ref<{ status?: number }>({})
const { items, indexPageData } = useIndex<SmsMessageListResource>(`sms-messages`, filters)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiClearableSelect v-model="filters.status" :items="yesNo('доставлено', 'не доставлено')" label="Статус доставки" density="comfortable" />
    </template>
    <SmsMessageList :items="items" />
  </UiIndexPage>
</template>
