<script setup lang="ts">
import type { SearchResultResource } from '~/components/Search'
import { mdiTextBoxSearchOutline } from '@mdi/js'
import { useDebounceFn } from '@vueuse/core'

interface Filters {
  q: string
}

const input = ref()

const filters = ref<Filters>({ q: '' })

const debounceKeydown = useDebounceFn(onKeydown, 300)

const { items, indexPageData, reloadData } = useIndex<SearchResultResource>(
  `search`,
  filters,
  {
    instantLoad: false,
    watchFilters: false,
  },
)

function onKeydown() {
  if (filters.value.q.length < 3) {
    items.value = []
    return
  }
  reloadData()
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
        placeholder="Введите текст..."
        variant="underlined"
      >
      </v-text-field>
    </template>
    <template #no-data>
      <UiNoData class="page-search__no-data">
        ничего не найдено
      </UiNoData>
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
      padding: 0px 10px 0 0 !important;
      font-size: 30px;
    }

    .v-field__outline {
      display: none !important;
    }

    .v-field__append-inner {
      padding: 14px 10px 0 !important;
    }
  }

  &__total {
    color: rgb(var(--v-theme-gray));
    white-space: nowrap;
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
