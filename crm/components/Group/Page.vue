<script setup lang="ts">
import type { Filters } from '~/components/Group/Filters.vue'

const { items, loading, onFiltersApply } = useIndex<GroupListResource, Filters>(`groups`)
</script>

<template>
  <div class="filters">
    <GroupFilters @apply="onFiltersApply" />
  </div>
  <div>
    <UiLoader3 :loading="loading" />
    <div class="table table--padding">
      <GroupList :items="items" />
    </div>
  </div>
</template>
