<script setup lang="ts">
import type { GroupDialog } from '#build/components'

const filters = ref<GroupFilters>({
  year: currentAcademicYear(),
})
const { items, indexPageData } = useIndex<GroupListResource, GroupFilters>(
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

    <div class="groups table table--padding">
      <GroupList :items="items" />
    </div>
  </UiIndexPage>
  <GroupDialog
    ref="groupDialog"
    @created="onGroupCreated"
  />
</template>
