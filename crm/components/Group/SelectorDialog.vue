<script setup lang="ts">
const emit = defineEmits<{
  select: [groupId: number, cvpId: number]
}>()
const { dialog, width } = useDialog('large')
const groups = ref<GroupListResource[]>([])
const contractVersionProgramId = ref<number>(-1)
const filters = ref<{
  year?: Year
  program?: Program
}>({})

function open(year: Year, program: Program, cvpId: number) {
  dialog.value = true
  contractVersionProgramId.value = cvpId
  filters.value = {
    year,
    program,
  }
}

async function onSelect(g: GroupListResource) {
  dialog.value = false
  emit('select', g.id, contractVersionProgramId.value)
}

async function loadData() {
  const { data } = await useHttp<ApiResponse<GroupListResource>>(
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
      <div class="dialog-body pt-0 ga-0">
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
        <GroupList
          :items="groups"
          selectable
          @select="onSelect"
        />
      </div>
    </div>
  </v-dialog>
</template>
