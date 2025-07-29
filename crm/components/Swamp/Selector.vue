<script setup lang="ts">
import type { SwampFilters } from './Filters.vue'

const { group } = defineProps<{ group: GroupResource }>()

const emit = defineEmits(['back', 'selected'])

const filters = ref<SwampFilters>({
  year: group.year,
  program: group.program,
  no_group: 1,
})

const { items, indexPageData } = useIndex<SwampListResource>(
  `swamps`,
  filters,
  {
    saveFilters: false,
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <SwampFilters v-model="filters" disabled />
    </template>
    <template #buttons>
      <v-btn color="primary" @click="emit('back')">
        <template #prepend>
          <v-icon icon="$back" />
        </template>
        назад
      </v-btn>
    </template>
    <SwampList :items="items" :group="group" show-client @selected="emit('selected')" />
  </UiIndexPage>
</template>
