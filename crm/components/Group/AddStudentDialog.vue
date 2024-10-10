<script setup lang="ts">
const { group } = defineProps<{ group: GroupResource }>()

const emit = defineEmits(['added'])

const { dialog, width } = useDialog('large')

const filters = ref<SwampFilters>({
  year: group.year,
  program: group.program,
  status: 'toFulfil',
})

const items = ref<SwampListResource[]>([])
const indexPageData = ref<IndexPageData>()

function open() {
  dialog.value = true
  const data = useIndex<SwampListResource, SwampFilters>(
      `swamps`,
      filters,
  )
  items.value = data.items.value
  indexPageData.value = data.indexPageData.value
}

async function onSelect(item: SwampListResource) {
  dialog.value = false
  await useHttp(`client-groups`, {
    method: 'post',
    body: {
      group_id: group.id,
      contract_version_program_id: item.id,
    },
  })
  emit('added')
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <span>
          Добавить в текущую группу
        </span>
        <v-btn
          icon="$close"
          :size="48"
          variant="text"
          @click="dialog = false"
        />
      </div>
      <div v-if="indexPageData" class="dialog-body py-0 ga-0">
        <UiIndexPage :data="indexPageData">
          <template #filters>
            <SwampFilters v-model="filters" disabled />
          </template>
          <SwampList :items="items" selectable @select="onSelect" />
        </UiIndexPage>
      </div>
    </div>
  </v-dialog>
</template>
