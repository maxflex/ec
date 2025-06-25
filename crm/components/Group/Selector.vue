<script setup lang="ts">
import type { GroupFilters } from './Filters.vue'

const { group } = defineProps<{ group: GroupResource }>()

const emit = defineEmits<{
  back: []
  selected: [g: GroupListResource]
}>()

const filters = ref<GroupFilters>({
  year: group.year,
  program: [group.program!],
})

const { items, indexPageData } = useIndex<GroupListResource>(
  `groups`,
  filters,
  {
    saveFilters: false,
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <GroupFilters v-model="filters" disabled />
    </template>
    <template #buttons>
      <v-btn color="primary" @click="emit('back')">
        <template #prepend>
          <v-icon icon="$back" />
        </template>
        назад
      </v-btn>
    </template>
    <GroupList :items="items" selectable @selected="g => emit('selected', g)" />
  </UiIndexPage>
</template>
