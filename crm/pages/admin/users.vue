<script setup lang="ts">
import type { UserDialog } from '#build/components'

const items = ref<UserResource[]>([])
const paginator = usePaginator()
const userDialog = ref<InstanceType<typeof UserDialog>>()
// const isLastPage = false

const loadData = async function () {
  console.log('loading data')
  paginator.page++
  paginator.loading = true
  const { data } = await useHttp<ApiResponse<[]>>('users', {
    params: { page: paginator.page },
  })
  paginator.loading = false
  if (data.value) {
    const { meta, data: newItems } = data.value
    items.value
      = paginator.page === 1 ? newItems : items.value?.concat(newItems)
    paginator.isLastPage = meta.current_page === meta.last_page
  }
}

async function onIntersect({
  done,
}: {
  done: (status: InfiniteScrollStatus) => void
}) {
  if (paginator.isLastPage) {
    return
  }
  done('loading')
  await loadData()
  done(paginator.isLastPage ? 'empty' : 'ok')
}

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

nextTick(loadData)
</script>

<template>
  <UiTopPanel>
    <v-spacer />
    <v-btn
      append-icon="$next"
      color="primary"
      @click="userDialog?.create()"
    >
      добавить
    </v-btn>
  </UiTopPanel>
  <UiLoader :paginator="paginator" />
  <v-infinite-scroll
    v-if="items"
    :margin="100"
    side="end"
    @load="onIntersect"
  >
    <UserList
      :items="items"
      @edit="userDialog?.edit"
    />
  </v-infinite-scroll>
  <UserDialog
    ref="userDialog"
    @updated="onUserUpdated"
    @destroyed="onUserDestroyed"
  />
</template>
