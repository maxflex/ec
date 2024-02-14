<script setup lang="ts">
import type { Groups } from "~/utils/models"

const items = ref<Groups>()
const paginator = usePaginator()
// const isLastPage = false

const Grade: Record<string, string> = {
  grade1: "1 класс",
  grade2: "2 класс",
  grade3: "3 класс",
  grade4: "4 класс",
  grade5: "5 класс",
  grade6: "6 класс",
  grade7: "7 класс",
  grade8: "8 класс",
  grade9: "9 класс",
  grade10: "10 класс",
  grade11: "11 класс",
  students: "студенты",
  other: "другие",
  external: "экстернат",
  school8: "школа 8 класс",
  school9: "школа 9 класс",
  school10: "школа 10 класс",
  online: "онлайн",
  practicum11: "практикум",
}

const Subject: Record<string, string> = {
  math: "математика",
  phys: "физика",
  chem: "химимя",
  bio: "биология",
  inf: "информатика",
  rus: "русский язык",
  lit: "литература",
  soc: "обществознание",
  his: "история",
  eng: "английский язык",
  geo: "география",
  soch: "сочинение",
}

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
