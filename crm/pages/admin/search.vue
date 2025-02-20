<script setup lang="ts">
import { mdiTextBoxSearchOutline } from '@mdi/js'
import type { SearchResultResource } from '~/components/Search'

interface Filters {
  q: string
}

const input = ref()

const filters = ref<Filters>({ q: '' })

const debounceKeydown = debounce(300, onKeydown)

const { items, total, indexPageData, reloadData } = useIndex<SearchResultResource, Filters>(
  `search`,
  filters,
  {
    instantLoad: false,
    watchFilters: false,
  },
)

async function onKeydown() {
  if (filters.value.q.length < 3) {
    total.value = undefined
    items.value = []
    return
  }
  await reloadData()
}

watch(() => filters.value.q, debounceKeydown)

onMounted(() => input.value.focus())
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <v-text-field
        ref="input"
        v-model="filters.q"
        placeholder="Поиск"
        prepend-inner-icon="$search"
        variant="underlined"
      >
        <template #append-inner>
          <div v-if="total !== undefined" class="page-search__total">
            {{ total }} найдено
          </div>
        </template>
      </v-text-field>
    </template>
    <template #no-data>
      <UiNoData class="page-search__no-data" :icon="mdiTextBoxSearchOutline" label="ничего не найдено" />
    </template>
    <SearchResults :items="items" />
  </UiIndexPage>
</template>

<style lang="scss">
.page-search {
  .filters {
    &__inputs {
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
  }

  &__total {
    color: rgb(var(--v-theme-gray));
    white-space: nowrap;
    font-size: 20px;
    opacity: 0.5;
  }

  &__no-data {
    font-size: 20px;
    .v-icon {
      font-size: 120px !important;
      opacity: 0.2 !important;
      left: -8px;
    }
  }
}
</style>
