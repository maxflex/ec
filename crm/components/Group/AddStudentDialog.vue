<script setup lang="ts">
const { group } = defineProps<{
  group: GroupResource
}>()

const emit = defineEmits(['updated'])
const candidates = ref<GroupCandidateResource[]>([])
const selected = ref<GroupCandidateResource[]>([])
const loading = ref(false)
const saving = ref(false)
const { dialog, width } = useDialog('medium')

function open() {
  selected.value = []
  dialog.value = true
  loadData()
}

function onSelect(candidate: GroupCandidateResource) {
  const index = selected.value.findIndex(c => c.id === candidate.id)
  index === -1 ? selected.value.push(candidate) : selected.value.splice(index, 1)
}

async function loadData() {
  loading.value = true
  const { data } = await useHttp<GroupCandidateResource[]>(
    `groups/candidates/${group.id}`,
  )
  if (data.value) {
    candidates.value = data.value
  }
  loading.value = false
}

async function save() {
  if (selected.value.length === 0) {
    dialog.value = false
    return
  }
  saving.value = true
  await useHttp(`groups/bulk-store-candidates/${group.id}`, {
    method: 'post',
    body: {
      ids: selected.value.map(e => e.contract_id),
    },
  })
  emit('updated')
  dialog.value = false
  setTimeout(() => (saving.value = false), 300)
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <span>
          Добавить учеников в группу
          <span
            v-if="selected.length"
            class="ml-1 text-gray"
          >
            {{ selected.length }}
          </span>
        </span>
        <v-btn
          icon="$save"
          :size="48"
          color="#fafafa"
          @click="save()"
        />
      </div>
      <UiLoaderr v-if="loading" />
      <div v-else class="dialog-body pt-0">
        <div class="table table--hover">
          <div v-for="candidate in candidates" :key="candidate.id" class="cursor-pointer" @click="onSelect(candidate)">
            <div style="width: 20px" class="vfn-1">
              <v-icon
                v-if="selected.includes(candidate)"
                color="secondary"
                icon="$checkboxOn"
              />
              <v-icon
                v-else
                color="gray"
                icon="$checkboxOff"
              />
            </div>
            <div style="width: 220px">
              {{ formatName(candidate.client) }}
            </div>
            <div style="width: 180px">
              договор №{{ candidate.contract_id }}
            </div>
            <div style="flex: 1">
              {{ ProgramShortLabel[candidate.program] }}
            </div>
            <div style="width: 90px; flex: initial">
              <span v-if="candidate.is_closed" class="text-error">
                расторгнут
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </v-dialog>
</template>
