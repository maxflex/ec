<script setup lang="ts">
import { mdiKeyboardBackspace } from '@mdi/js'

const { group } = defineProps<{ group: GroupResource }>()

const emit = defineEmits(['back', 'selected'])

const filters = ref<SwampFilters>({
  year: group.year,
  program: group.program,
  status: 'toFulfil',
})

const { items, indexPageData } = useIndex<SwampListResource, SwampFilters>(
    `swamps`,
    filters,
    {
      disableSaveFilters: true,
    },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <SwampFilters v-model="filters" disabled />
    </template>
    <SwampList :items="items" :group="group" @selected="emit('selected')" />
    <div class="table">
      <div style="border-bottom: none">
        <UiIconLink :icon="mdiKeyboardBackspace" prepend @click="emit('back')">
          назад к списку учеников
        </UiIconLink>
      </div>
    </div>
  </UiIndexPage>
</template>
