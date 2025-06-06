<script setup lang="ts">
import type { SmsMessageListResource } from '~/components/SmsMessage'

const filters = ref<{ status?: number }>({})
const { items, indexPageData } = useIndex<SmsMessageListResource>(`sms-messages`, filters)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiClearableSelect :items="yesNo('доставлено', 'не доставлено')" v-model="filters.status" label="Статус доставки" density="comfortable" />
    </template>
    <SmsMessageList :items="items" />
  </UiIndexPage>
</template>
