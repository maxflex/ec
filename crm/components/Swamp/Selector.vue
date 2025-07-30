<script setup lang="ts">
import type { SwampListResource } from '.'
import type { SwampFilters } from './Filters.vue'

const { group } = defineProps<{ group: GroupResource }>()

const emit = defineEmits(['back', 'updated'])

const filters = ref<SwampFilters>({
  year: group.year,
  program: group.program,
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
    <SwampListSelector :items="items" :group="group" show-client @updated="emit('updated')" />
  </UiIndexPage>
</template>
