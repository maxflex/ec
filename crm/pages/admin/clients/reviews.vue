<script setup lang="ts">
const items = ref([])
const paginator = usePaginator()
// const isLastPage = false

const loadData = async function () {
  console.log('loading data')
  paginator.page++
  paginator.loading = true
  const { data } = await useHttp<ApiResponse<[]>>('client-reviews', {
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
  done('loading')
  await loadData()
  done('ok')
}

nextTick(loadData)
</script>

<template>
  <UiLoader :paginator="paginator" />
  <v-infinite-scroll
    v-if="items"
    :margin="100"
    class="table"
    side="end"
    @load="onIntersect"
  >
    <div
      v-for="item in items"
      :key="item.id"
    >
      <div style="width: 50px">
        {{ item.id }}
      </div>
      <div style="width: 200px">
        <NuxtLink :to="{ name: 'clients-id', params: { id: item.client_id } }">
          {{ formatName(item.client) }}
        </NuxtLink>
      </div>
      <div style="width: 350px">
        <NuxtLink :to="{ name: 'teachers-id', params: { id: item.teacher_id } }">
          {{ formatFullName(item.teacher) }}
        </NuxtLink>
      </div>
      <div style="width: 250px">
        {{ ProgramLabel[item.program] }}
      </div>
      <div>
        <UiRating v-model="item.rating" />
      </div>
      <!-- <div style="width: 350px">
        <NuxtLink :to="{ name: 'teachers-id', params: { id: item.id } }">
          {{ formatFullName(item) }}
        </NuxtLink>
      </div>
      <div style="width: 250px">
        {{ TeacherStatusLabel[item.status] }}
      </div>
      <div>
        {{ item.subjects.map(s => SubjectLabelShort[s]).join('+') }}
      </div> -->
      <div class="text-right text-gray">
        {{ formatDateTime(item.created_at) }}
      </div>
    </div>
  </v-infinite-scroll>
</template>
