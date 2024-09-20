<script setup lang="ts">
import type { UserDialog } from '#build/components'

const userDialog = ref<InstanceType<typeof UserDialog>>()
const filters = ref<UserFilters>(loadFilters({}))
const { items, indexPageData } = useIndex<UserResource, UserFilters>(`users`, filters)

function onUserUpdated(u: UserResource) {
  const index = items.value.findIndex(e => e.id === u.id)
  if (index !== -1) {
    items.value[index] = u
  }
  else {
    items.value.unshift(u)
    smoothScroll('main', 'top')
  }
}

function onUserDestroyed(u: UserResource) {
  const index = items.value.findIndex(e => e.id === u.id)
  if (index !== -1) {
    items.value.splice(index, 1)
  }
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UserFilters v-model="filters" />
    </template>
    <template #buttons>
      <v-btn
        append-icon="$next"
        color="primary"
        @click="userDialog?.create()"
      >
        добавить
      </v-btn>
    </template>
    <UserList
      :items="items"
      @edit="userDialog?.edit"
    />
  </UiIndexPage>
  <UserDialog
    ref="userDialog"
    @updated="onUserUpdated"
    @destroyed="onUserDestroyed"
  />
</template>
