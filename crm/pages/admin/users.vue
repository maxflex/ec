<script setup lang="ts">
import type { UserDialog } from '#build/components'
import type { Filters } from '~/components/User/Filters.vue'

const items = ref<UserResource[]>([])
const userDialog = ref<InstanceType<typeof UserDialog>>()
const filters = ref<Filters>({})
const loading = ref(false)
let page = 0
let isLastPage = false
let scrollContainer: HTMLElement | null = null
// const isLastPage = false

async function loadData() {
  if (loading.value || isLastPage) {
    return
  }
  page++
  loading.value = true
  const { data } = await useHttp<ApiResponse<UserResource[]>>('users', {
    params: {
      page,
      ...filters.value,
    },
  })
  if (data.value) {
    const { meta, data: newItems } = data.value
    items.value = page === 1 ? newItems : items.value.concat(newItems)
    isLastPage = meta.current_page >= meta.last_page
  }
  loading.value = false
}

function onFiltersApply(f: Filters) {
  filters.value = f
  page = 0
  isLastPage = false
  loadData()
}

function onScroll() {
  if (!scrollContainer || loading.value) {
    return
  }
  const { scrollTop, scrollHeight, clientHeight } = scrollContainer
  const scrollPosition = scrollTop + clientHeight
  const scrollThreshold = scrollHeight * 0.9

  if (scrollPosition >= scrollThreshold) {
    loadData()
  }
}

onMounted(() => {
  scrollContainer = document.documentElement.querySelector('main')
  scrollContainer?.addEventListener('scroll', onScroll)
})

onUnmounted(() => {
  scrollContainer?.removeEventListener('scroll', onScroll)
})

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
  <div class="filters">
    <UserFilters @apply="onFiltersApply" />
    <v-btn
      append-icon="$next"
      color="primary"
      @click="userDialog?.create()"
    >
      добавить
    </v-btn>
  </div>
  <div>
    <UiLoader3 :loading="loading" />
    <UserList
      :items="items"
      @edit="userDialog?.edit"
    />
  </div>
  <UserDialog
    ref="userDialog"
    @updated="onUserUpdated"
    @destroyed="onUserDestroyed"
  />
</template>
