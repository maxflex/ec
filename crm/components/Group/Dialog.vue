<script setup lang="ts">
import { clone } from 'lodash-es'

const emit = defineEmits<{
  created: [g: GroupListResource]
  updated: [g: GroupResource]
  deleted: [g: GroupResource]
}>()

const modelDefaults: GroupResource = {
  id: newId(),
  year: currentAcademicYear(),
  teachers: [],
  zoom: {
    id: null,
    password: null,
  },
}

const { dialog, width } = useDialog('default')
const loading = ref(false)
const itemId = ref<number>()
const group = ref<GroupResource>(modelDefaults)

function open(r: GroupResource) {
  group.value = clone(r)
  dialog.value = true
}

function create() {
  itemId.value = undefined
  open(modelDefaults)
}

async function edit(r: GroupResource) {
  itemId.value = r.id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<GroupResource>(`groups/${r.id}`)
  if (data.value) {
    open(data.value)
  }
  loading.value = false
}

async function save() {
  dialog.value = false
  if (itemId.value) {
    const { data } = await useHttp<GroupResource>(`groups/${itemId.value}`, {
      method: 'put',
      body: group.value,
    })
    if (data.value) {
      emit('updated', data.value)
    }
  }
  else {
    const { data } = await useHttp<GroupListResource>(`groups`, {
      method: 'post',
      body: group.value,
    })
    if (data.value) {
      emit('created', data.value)
    }
  }
}

defineExpose({ create, edit })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div
      v-if="group"
      class="dialog-wrapper"
    >
      <div class="dialog-header">
        <div v-if="itemId">
          Редактирование группы
          <div class="dialog-subheader">
            {{ group.user ? formatName(group.user) : 'неизвестно' }}
            <template v-if="group.created_at">
              {{ formatDateTime(group.created_at) }}
            </template>
          </div>
        </div>
        <template v-else>
          Новая группа
        </template>
        <div>
          <DialogDeleteBtn
            :id="itemId"
            api-url="groups"
            confirm-text="Вы уверены, что хотите удалить группу?"
            @deleted="dialog = false"
          />
          <v-btn
            :size="48"
            icon="$save"
            variant="text"
            @click="save()"
          />
        </div>
      </div>
      <UiLoader v-if="loading" />
      <div
        v-else
        class="dialog-body"
      >
        <div>
          <UiClearableSelect
            v-model="group.year"
            label="Учебный год"
            :items="selectItems(YearLabel)"
          />
        </div>
        <div>
          <UiClearableSelect
            v-model="group.program"
            label="Программа"
            :items="selectItems(ProgramLabel)"
          />
        </div>
        <div>
          <v-text-field
            v-model="group.lessons_planned"
            type="number"
            hide-spin-buttons
            label="Занятий по программе"
          />
        </div>
        <div class="double-input">
          <v-text-field
            v-model="group.zoom.id"
            label="Zoom логин"
          />
          <v-text-field
            v-model="group.zoom.password"
            label="Zoom пароль"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
