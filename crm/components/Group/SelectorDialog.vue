<script setup lang="ts">
const emit = defineEmits(['select'])
const { dialog, width } = useDialog('large')
const groups = ref<GroupListResource[]>([])
const swamp = ref<SwampListResource>()
const filters = ref<{
  year?: Year
  program?: Program
}>({})

function open(s: SwampListResource) {
  swamp.value = s
  dialog.value = true
  filters.value = {
    year: s.year,
    program: s.program,
  }
}

async function onSelect(g: GroupListResource) {
  dialog.value = false
  await useHttp(`client-groups`, {
    method: 'post',
    params: {
      group_id: g.id,
      contract_version_program_id: swamp.value!.id,
    },
  })
  emit('select')
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
