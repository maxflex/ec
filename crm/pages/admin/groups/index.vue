<script setup lang="ts">
import type { GroupDialog, GroupFilters } from '#build/components'

const items = ref<GroupListResource[]>()
const paginator = usePaginator()
const groupDialog = ref<null | InstanceType<typeof GroupDialog>>()
let filters = loadFilters<GroupFilters>({})

async function loadData() {
  if (paginator.loading) {
    return
  }
  paginator.page++
  paginator.loading = true
  console.log('page', paginator.page)
  const { data } = await useHttp<ApiResponse<GroupListResource[]>>('groups', {
    params: {
      page: paginator.page,
      ...filters,
    },
  })
  paginator.loading = false
  if (data.value) {
    const { meta, data: newItems } = data.value
    items.value
      = paginator.page === 1 ? newItems : items.value?.concat(newItems)
    paginator.isLastPage = meta.current_page >= meta.last_page
  }
}

function onFiltersApply(f: GroupFilters) {
  filters = f
  paginator.page = 0
  loadData()
}

function onGroupCreated(r: GroupListResource) {
  items.value?.unshift(r)
}

// оставлю как пример
// parameter destruction + type declaration
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

nextTick(loadData)
</script>

<template>
  <div class="filters">
    <GroupFilters @apply="onFiltersApply" />
    <a
      class="cursor-pointer"
      @click="groupDialog?.create()"
    >
      добавить группу
    </a>
  </div>
  <UiLoader :paginator="paginator" />
  <div
    v-if="items"
    class="groups"
  >
    <v-infinite-scroll
      :margin="100"
      color="gray"
      side="end"
      class="table table--padding"
      @load="onIntersect"
    >
      <GroupList :items="items" />
    </v-infinite-scroll>
  </div>
  <GroupDialog
    ref="groupDialog"
    @created="onGroupCreated"
  />
</template>
