<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  created: [e: InstructionListResource]
  updated: [e: InstructionResource ]
  deleted: [e: InstructionBaseResource]
}>()
const QuillEditor = defineAsyncComponent({
  loader: () =>
    import('@vueup/vue-quill').then(VueQuill => VueQuill.QuillEditor),
})
const { width, dialog } = useDialog('large')
const deleting = ref(false)
const saving = ref(false)
const loading = ref(false)
const itemId = ref<number>()
const modelDefaults: InstructionBaseResource = {
  id: newId(),
  title: '',
  text: null,
}
const item = ref<InstructionBaseResource>(modelDefaults)

function create() {
  itemId.value = undefined
  item.value = clone(modelDefaults)
  dialog.value = true
}

function addVersion(i: InstructionResource) {
  const { title, text, entry_id: entryId } = i
  itemId.value = undefined
  item.value = {
    id: newId(),
    title,
    text,
    entry_id: entryId,
  }
  dialog.value = true
}

async function edit(i: InstructionResource) {
  const { id, title, text } = i
  itemId.value = id
  item.value = { id, title, text }
  dialog.value = true
}

async function save() {
  saving.value = true
  // обновление
  if (itemId.value) {
    const { data } = await useHttp<InstructionResource>(`instructions/${itemId.value}`, {
      method: 'put',
      body: item.value,
    })
    if (data.value) {
      emit('updated', data.value)
    }
  }
  else {
    const { data } = await useHttp<InstructionListResource>(`instructions`, {
      method: 'post',
      body: item.value,
    })
    if (data.value) {
      // была добавлена новая версия – редирект на её страницу
      if (item.value.entry_id) {
        useRouter().push({
          name: 'instructions-id',
          params: {
            id: data.value.id,
          },
        })
        return
      }
      emit('created', data.value)
    }
  }
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить инструкцию?')) {
    return
  }
  deleting.value = true
  const { data, status } = await useHttp(`instructions/${item.value.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    deleting.value = false
  }
  else if (data.value) {
    emit('deleted', item.value)
    dialog.value = false
    setTimeout(() => deleting.value = false, 300)
  }
}

defineExpose({ create, edit, addVersion })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <template v-if="itemId">
          Редактировать инструкцию
        </template>
        <template v-else-if="item.entry_id">
          Новая версия инструкции
        </template>
        <template v-else>
          Новая инструкция
        </template>
        <v-btn
          icon="$save"
          :size="48"
          color="#fafafa"
          @click="save()"
        />
      </div>
      <UiLoaderr v-if="loading" />
      <div v-else class="dialog-body">
        <div>
          <v-text-field v-model="item.title" label="Заголовок" />
        </div>
        <div>
          <QuillEditor
            v-model:content="item.text"
            theme="snow"
            content-type="html"
            :toolbar="[{ header: 1 }, 'bold', 'italic', 'underline']"
          />
        </div>
        <!-- <div
          v-if="itemId && false"
          class="dialog-bottom"
        >
          <span>
            оценка создана
            {{ formatName(item.user!) }}
            {{ formatDateTime(item.created_at!) }}
          </span>
          <v-btn
            icon="$delete"
            :size="48"
            color="red"
            variant="plain"
            :loading="deleting"
            @click="destroy()"
          />
        </div> -->
      </div>
    </div>
  </v-dialog>
</template>
