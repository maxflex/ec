<script setup lang="ts">
const emit = defineEmits<{
  select: [g: GroupListResource, contractId: number]
}>()
const { dialog, width } = useDialog('large')
let contractId: number
const groups = ref<GroupListResource[]>([])
const filters = ref<{
  year?: Year
  program?: Program
}>({})

function open(p: Program, y: Year, _contractId: number) {
  dialog.value = true
  contractId = _contractId
  filters.value = {
    year: y,
    program: p,
  }
}

function onSelect(g: GroupListResource) {
  dialog.value = false
  emit('select', g, contractId)
  useHttp(`client-groups`, {
    method: 'post',
    params: {
      group_id: g.id,
      contract_id: contractId,
    },
  })
}

async function loadData() {
  const { data } = await useHttp<ApiResponse<GroupListResource[]>>(
    'groups',
    {
      params: filters.value,
    },
  )
  if (data.value) {
    groups.value = data.value.data
  }
}

watch(filters, loadData, { deep: true })

defineExpose({ open })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div class="dialog-wrapper">
      <div class="dialog-header">
        Выберите группу
        <!--                <v-btn icon="$close" :size="48" variant="text" @click="dialog = false" /> -->
      </div>
      <div class="dialog-body pt-0">
        <UiFilters>
          <v-select
            v-model="filters.year"
            label="Учебный год"
            :items="selectItems(YearLabel)"
            density="comfortable"
          />
          <UiClearableSelect
            v-model="filters.program"
            label="Программа"
            :items="selectItems(ProgramLabel)"
            density="comfortable"
          />
        </UiFilters>
        <div class="table table--hover table--padding">
          <GroupList
            :items="groups"
            selectable
            @select="onSelect"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
