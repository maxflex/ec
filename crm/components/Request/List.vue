<script setup lang="ts">
import { Vue3SlideUpDown } from 'vue3-slide-up-down'
import type { RequestDialog } from '#build/components'

const model = defineModel<RequestListResource[]>({ default: () => [] })
const requestDialog = ref<null | InstanceType<typeof RequestDialog>>()
const expanded = ref<{ [key: number]: RequestListResource[] }>({})
const expandingId = ref<number>()

function onRequestUpdated(r: RequestListResource) {
  const index = model.value.findIndex(e => e.id === r.id)
  if (index !== -1) {
    model.value[index] = r
  }
  else {
    model.value.unshift(r)
  }
  itemUpdated('request', r.id)
}

function onRequestDeleted(r: RequestResource) {
  const index = model.value.findIndex(e => e.id === r.id)
  if (index !== -1) {
    model.value.splice(index, 1)
  }
}

async function expand(r: RequestListResource) {
  if (r.associated_requests_count === 0) {
    return
  }
  if (expanded.value[r.id]) {
    delete expanded.value[r.id]
    return
  }
  expandingId.value = r.id
  const { data } = await useHttp<RequestListResource[]>(`requests/associated/${r.id}`)
  if (data.value) {
    expanded.value[r.id] = data.value
  }
  expandingId.value = undefined
}
</script>

<template>
  <div class="table request-list">
    <template v-for="item in model" :key="item.id">
      <RequestItem
        :item="item"
        :class="{
          'request-list--expanded': expanded[item.id],
        }"
        :expanding="expandingId === item.id"
        @edit="requestDialog?.edit"
        @expand="expand"
      />

      <Vue3SlideUpDown
        class="table"
        :model-value="!!expanded[item.id]"
        :duration="200"
      >
        <RequestItem
          v-for="r in expanded[item.id]"
          :key="r.id"
          :item="r"
          @edit="requestDialog?.edit"
        />
      </Vue3SlideUpDown>
    </template>
  </div>
  <RequestDialog
    ref="requestDialog"
    @updated="onRequestUpdated"
    @deleted="onRequestDeleted"
  />
</template>

<style lang="scss">
.request-list {
  .slide-up-down__container {
    background: rgba(var(--v-theme-secondary), 0.1);
    .request-item__actions > .badge:last-child {
      pointer-events: none;
      .v-badge {
        display: none !important;
      }
    }
  }
  &--expanded {
    background: rgba(var(--v-theme-secondary), 0.2);
  }
  & > div {
    transition: background-color ease-in-out 0.2s;
  }
}
</style>
