<script setup lang="ts">
import type { SmsListResource } from '~/components/Sms'

const { items, indexPageData } = useIndex<SmsListResource>(`sms-messages`)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <SmsList :items="items" />
  </UiIndexPage>
</template>
