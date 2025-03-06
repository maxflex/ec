<script setup lang="ts">
const { id } = defineProps<{ id: number }>()
const dialog = ref()

const { items, indexPageData } = useIndex<GroupActResource>(
  `group-acts`,
  ref({}),
  {
    staticFilters: {
      group_id: id,
    },
  },
)

function onUpdated(item: GroupActResource) {
  const index = items.value.findIndex(e => e.id === item.id)
  index === -1
    ? items.value.unshift(item)
    : items.value.splice(index, 1, item)
  itemUpdated('group-act', item.id)
}

function onDeleted(item: GroupActResource) {
  const index = items.value.findIndex(e => e.id === item.id)
  items.value.splice(index, 1)
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #buttons>
      <v-btn color="primary" @click="dialog.create(id)">
        добавить
      </v-btn>
    </template>
    <GroupActList :items="items" @edit="dialog.edit" />
  </UiIndexPage>
  <GroupActDialog ref="dialog" @updated="onUpdated" @deleted="onDeleted" />
</template>
