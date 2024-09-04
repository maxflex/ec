<script setup lang="ts">
import type { GroupDialog } from '#build/components'
import type { Filters } from '~/components/Group/Filters.vue'

const { items, loading, onFiltersApply } = useIndex<GroupListResource, Filters>('groups', {
  defaultFilters: {
    year: currentAcademicYear(),
  },
})

const groupDialog = ref<null | InstanceType<typeof GroupDialog>>()

function onGroupCreated(g: GroupListResource) {
  items.value?.unshift(g)
  itemUpdated('group', g.id)
}
</script>

<template>
  <UiFilters>
    <GroupFilters @apply="onFiltersApply" />
    <template #buttons>
      <v-btn
        color="primary"
        @click="groupDialog?.create()"
      >
        добавить группу
      </v-btn>
    </template>
  </UiFilters>
  <UiLoader3 :loading="loading" />
  <div class="groups table table--padding">
    <GroupList :items="items" />
  </div>
  <GroupDialog
    ref="groupDialog"
    @created="onGroupCreated"
  />
</template>
