<script setup lang="ts">
import type { PassDialog } from '#components'
import type { PassResource } from '~/components/Pass'
import type { PassFilters } from '~/components/Pass/Filters.vue'

const passDialog = ref<InstanceType<typeof PassDialog>>()
const filters = ref<PassFilters>(loadFilters({
  direction: [],
}))
const { items, indexPageData } = useIndex<PassResource, PassFilters>(`passes`, filters)

function onPassCreated(pass: PassResource) {
  items.value.unshift(pass)
  itemUpdated('pass', pass.id)
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <PassFilters v-model="filters" />
    </template>
    <template #buttons>
      <v-btn color="primary" @click="passDialog?.create()">
        добавить пропуск
      </v-btn>
    </template>
    <PassList :items="items" />
  </UiIndexPage>
  <PassDialog
    ref="passDialog"
    @created="onPassCreated"
  />
</template>
