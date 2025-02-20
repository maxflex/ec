<script setup lang="ts">
import { mdiTextBoxSearchOutline } from '@mdi/js'
import type { SearchResultResource } from '~/components/Search'

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
  <UiFilters class="search-input">
    <v-text-field
      ref="input"
      v-model="q"
      placeholder="Поиск"
      prepend-inner-icon="$search"
      variant="underlined"
    >
      <template #append-inner>
        <div v-if="total !== undefined" class="search-input__total">
          {{ total }} найдено
        </div>
      </template>
    </v-text-field>
  </UiFilters>

  <UiNoData v-if="total === 0" class="search-no-data" :icon="mdiTextBoxSearchOutline" label="ничего не найдено" />
  <SearchResults v-else :items="items" />
</template>

<style lang="scss">
.search {
  &-no-data {
    font-size: 20px;
    .v-icon {
      font-size: 120px !important;
      opacity: 0.2 !important;
      left: -8px;
    }
  }
  &-input {
    // justify-content: center;
    .filters__inputs {
      width: 100% !important;
      // width: 50% !important;
    }

    .v-field__prepend-inner {
      padding-top: 10px !important;
      font-size: 18px;
    }

    .v-field__input {
      padding: 0px 10px !important;
      font-size: 20px;
    }

    .v-field__append-inner {
      padding: 10px 10px 0 !important;
    }

    &__total {
      color: rgb(var(--v-theme-gray));
      white-space: nowrap;
      font-size: 20px;
      opacity: 0.5;
    }
  }
}
</style>
