<script setup lang="ts">
import type { GroupDialog } from '#build/components'
import type { GroupListResource } from '~/components/Group'
import type { GroupFilters } from '~/components/Group/Filters.vue'

const filters = ref<GroupFilters>(loadFilters({
  year: currentAcademicYear(),
  program: [],
}))
const { items, indexPageData } = useIndex<GroupListResource>(
  `groups`,
  filters,
)

const groupDialog = ref<null | InstanceType<typeof GroupDialog>>()

function onGroupCreated(g: GroupListResource) {
  items.value?.unshift(g)
  itemUpdated('group', g.id)
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <GroupFilters v-model="filters" />
    </template>
    <template #buttons>
      <v-btn
        color="primary"
        @click="groupDialog?.create()"
      >
        добавить группу
      </v-btn>
    </template>
    <GroupList :items="items" />
  </UiIndexPage>
  <GroupDialog
    ref="groupDialog"
    @created="onGroupCreated"
  />
</template>
