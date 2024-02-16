<script setup lang="ts">
import type { Groups } from "~/utils/models"
import { Grade, Subject } from "~/utils/sment"

const items = ref<Groups>()
const paginator = usePaginator()

const loadData = async function () {
  paginator.page++
  paginator.loading = true
  const { data } = await useHttp<ApiResponse<Groups>>("groups", {
    params: { page: paginator.page },
  })
  paginator.loading = false
  if (data.value) {
    const { meta, data: newItems } = data.value
    items.value =
      paginator.page === 1 ? newItems : items.value?.concat(newItems)
    paginator.isLastPage = meta.current_page === meta.last_page
  }
}

onMounted(async () => {
  // этот баг только при ssr: true
  // https://github.com/vuejs/core/issues/6638
  // https://github.com/nuxt/nuxt/issues/25131
  // await nextTick()

  await loadData()
})

async function onIntersect({
  done,
}: {
  done: (status: InfiniteScrollStatus) => void
}) {
  done("loading")
  await loadData()
  done("ok")
}
</script>

<template>
  <UiLoader :paginator="paginator" />
  <v-infinite-scroll
    :onLoad="onIntersect"
    :margin="100"
    class="table table--two-lines"
    v-if="items"
  >
    <div class="groups__item" v-for="item in items" :key="item.id">
      <div>
        <div style="width: 200px">
          <NuxtLink :to="{ name: 'groups-id', params: { id: item.id } }">
            Группа {{ item.id }}
          </NuxtLink>
        </div>
        <div style="width: 250px">
          <a href="#">
            {{ formatName(item.teacher) }}
          </a>
        </div>
        <div style="width: 200px">
          {{ Grade[item.grade] }}
        </div>
        <div style="width: 200px">
          {{ Subject[item.subject] }}
        </div>
        <div>{{ item.lessons_planned }} уроков</div>
      </div>
      <div v-if="item.zoom">
        <div class="text-gray">
          Идентификатор ZOOM: {{ item.zoom.id }} <br />
          Пароль ZOOM: {{ item.zoom.password }}
        </div>
      </div>
    </div>
  </v-infinite-scroll>
</template>

<style lang="scss">
.groups {
}
</style>
