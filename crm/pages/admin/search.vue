<script setup lang="ts">
import { mdiTextBoxSearchOutline } from '@mdi/js'

const key = ref(0)
const q = ref('')
const input = ref()
const items = ref<SearchResultResource[]>([])
const total = ref<number>()

const debounceSearch = debounce(300, search)

async function search() {
  if (q.value.length < 3) {
    total.value = undefined
    items.value = []
  }
  else {
    const { data } = await useHttp<ApiResponse<SearchResultResource>>(
        `search`,
        {
          params: {
            q: q.value,
          },
        },
    )
    if (data.value) {
      items.value = data.value.data
      total.value = data.value.meta.total
    }
  }
  key.value++
}

watch(q, debounceSearch)

onMounted(() => input.value.focus())
</script>

<template>
  <UiFilters>
    <v-text-field
      ref="input"
      v-model="q"
      placeholder="Поиск"
      prepend-inner-icon="$search"
      style="width: 40vw !important"
      rounded
    >
      <template #append-inner>
        <div v-if="total !== undefined" class="search-total">
          {{ total }} найдено
        </div>
      </template>
    </v-text-field>
  </UiFilters>

  <UiNoData v-if="total === 0" class="search-no-data" :icon="mdiTextBoxSearchOutline" label="ничего не найдено" />
  <SearchList v-else :key="key" :q="q" :items="items" />
</template>

<style lang="scss">
.search {
  &-total {
    color: rgb(var(--v-theme-placeholder));
    white-space: nowrap;
    padding-right: 8px;
  }
  &-no-data {
    .v-icon {
      font-size: 120px !important;
      opacity: 0.2 !important;
    }
  }
}
</style>
