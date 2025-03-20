<script setup lang="ts">
const modelDefaults: InstructionBaseResource = {
  id: newId(),
  title: '',
  text: '',
  status: 'draft',
}
const titleInput = ref()
const route = useRoute()
const router = useRouter()
const saving = ref(false)
const deleting = ref(false)
const loading = ref(true)
const item = ref<InstructionBaseResource>(modelDefaults)
const { isTeacher } = useAuthStore()
const apiUrl = isTeacher ? 'instructions-check' : 'instructions'
const { showGlobalMessage } = useGlobalMessage()

const teacherAvailableStatuses: InstructionStatus[] = [
  'toCheckTeacher',
  'finalCheckBeforePublished',
]

const mode: 'new-version' | 'create' | 'edit' = (function () {
  switch (route.name) {
    case 'instructions-id-new-version':
      return 'new-version'
    case 'instructions-create':
      return 'create'
    default:
      return 'edit'
  }
})()

async function save() {
  saving.value = true
  switch (mode) {
    case 'create':
    case 'new-version':
      const { data } = await useHttp<InstructionListResource>(
        apiUrl,
        {
          method: 'post',
          body: item.value,
        },
      )
      if (data.value) {
        router.push({
          name: 'instructions-id',
          params: {
            id: data.value.id,
          },
        })
      }
      break
    case 'edit':
      await useHttp<InstructionListResource>(
        `${apiUrl}/${item.value.id}`,
        {
          method: 'put',
          body: item.value,
        },
      )
      setTimeout(() => (saving.value = false), 200)
      showGlobalMessage('Инструкция сохранена', 'success')
  }
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить инструкцию?')) {
    return
  }
  deleting.value = true
  const { status } = await useHttp(
    `${apiUrl}/${item.value.id}`,
    {
      method: 'delete',
    },
  )
  if (status.value === 'error') {
    deleting.value = false
    return
  }
  router.push({ name: 'instructions' })
}

async function loadData() {
  loading.value = true
  if (mode === 'create') {
    setTimeout(() => titleInput?.value.focus(), 50)
  }
  else {
    const { data } = await useHttp<InstructionResource>(
      `${apiUrl}/${route.params.id}`,
    )
    item.value = data.value as InstructionResource
  }
  loading.value = false
}

nextTick(loadData)
</script>

<template>
  <div v-if="!loading" class="instruction-edit">
    <div class="instruction-edit__title">
      <v-text-field ref="titleInput" v-model="item.title" label="Заголовок" />

      <v-select
        v-if="isTeacher"
        v-model="item.status"
        :disabled="item.status === 'published'"
        :items="teacherAvailableStatuses.map(value => ({
          value,
          title: InstructionStatusLabel[value],
        }))"
        label="Статус"
      >
        <template v-if="!(item.status in teacherAvailableStatuses)" #selection>
          {{ InstructionStatusLabel[item.status] }}
        </template>
      </v-select>
      <v-select
        v-else
        v-model="item.status"
        :items="selectItems(InstructionStatusLabel)"
        label="Статус"
      />
      <div class="text-right">
        <v-btn
          v-if="mode !== 'create' && !isTeacher"
          icon="$delete"
          :size="48"
          :loading="deleting"
          class="remove-btn"
          @click="destroy()"
        />
        <v-btn v-if="!(isTeacher && item.status === 'published')" icon="$save" :size="48" :loading="saving" @click="save()" />
      </div>
    </div>
    <div>
      <InstructionWysiwyg v-model="item.text" />
    </div>
  </div>
</template>

<style lang="scss">
.instruction-edit {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 30px;
  &__title {
    display: flex;
    gap: 20px;
    align-items: center;
    justify-content: space-between;
    & > div {
      flex: 1;
    }
  }
  .ql-editor {
    min-height: 80vh;
  }
}
</style>
