<script setup lang="ts">
import type { TestDialog } from '#build/components'
import type { ClientTestResource } from '~/components/ClientTest'

definePageMeta({ middleware: ['check-active-test'] })

const testDialog = ref<InstanceType<typeof TestDialog>>()

const { items, indexPageData, reloadData } = useIndex<ClientTestResource>(`client-tests`)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <ClientTestList
      v-if="items"
      :items="items"
      @open="testDialog?.open"
    />
  </UiIndexPage>
  <TestDialog
    ref="testDialog"
    @updated="reloadData"
  />
</template>
